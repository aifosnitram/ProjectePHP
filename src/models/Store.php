<?php
    namespace models;
    use config\Database;
    class Store extends Model{
        protected static string $table = 'stores';

        //Constructor
        // L'ordre dels paràmetres ha de coincidir EXACTAMENT amb l'ordre de les
        // columnes de la taula: Model::all() fa new static(...array_values($row)),
        // és a dir, passa els valors per posició.
        public function __construct(
            public ?int     $store_id = null,
            public ?string 	$store_name = null,
            public ?string 	$web_address = null,
            public ?string 	$address_line1 = null,
            public ?string 	$city = null,
            public ?string 	$state_province = null,
            public ?string 	$postal_code = null,
            public ?string 	$country = null,
            public ?string 	$latitude = null,
            public ?string 	$longitude = null,
            public ?string 	$logo = null,
            public ?string 	$logo_mime_type = null,
            public ?string 	$logo_filename = null,
            public ?string 	$logo_charset = null,
            public ?string 	$logo_last_updated = null
        ){ }


        public function save (){
            // $db es declara fora del try: si el constructor de Database falla,
            // catch i finally s'executen igualment i la variable no existiria.
            $db = null;
            try {
                // Connectar a la base de dades
                $db = new Database();
                $db->connectDB(BASE_PATH . '/config.db');

                // Obtenir el nom de la taula de la classe filla
                $table = static::$table;

                // store_name és UNIQUE. Sense aquesta comprovació, l'INSERT ...
                // ON DUPLICATE KEY UPDATE de més avall no fallaria: actualitzaria
                // la BOTIGA QUE JA TÉ AQUELL NOM, que no és la que estem desant.
                // Si store_id és null (botiga nova), qualsevol coincidència és
                // un duplicat; si no, s'ignora la mateixa fila que modifiquem.
                $sql = "SELECT store_id FROM $table WHERE store_name = ? AND (? IS NULL OR store_id <> ?) LIMIT 1";
                $stmt = $db->conn->prepare($sql);
                $id = $this->store_id;
                $stmt->bind_param("sii", $this->store_name, $id, $id);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->close();
                    throw new \DomainException("Ja existeix una botiga amb el nom \"{$this->store_name}\".");
                }
                $stmt->close();

                $sql = "INSERT INTO $table (store_id, store_name, web_address, address_line1, city, state_province, postal_code, country, latitude, longitude, logo, logo_mime_type, logo_filename, logo_charset, logo_last_updated)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        store_name = VALUES(store_name),
                        web_address = VALUES(web_address),
                        address_line1 = VALUES(address_line1),
                        city = VALUES(city),
                        state_province = VALUES(state_province),
                        postal_code = VALUES(postal_code),
                        country = VALUES(country),
                        latitude = VALUES(latitude),
                        longitude = VALUES(longitude),
                        logo = VALUES(logo),
                        logo_mime_type = VALUES(logo_mime_type),
                        logo_filename = VALUES(logo_filename),
                        logo_charset = VALUES(logo_charset),
                        logo_last_updated = VALUES(logo_last_updated)";
                $stmt = $db->conn->prepare($sql);
                // bind_param necessita variables per referència.
                // Si store_id és null, enviem NULL i AUTO_INCREMENT fa la resta.
                $id      = $this->store_id;
                $name    = $this->store_name;
                $web     = $this->web_address;
                $address = $this->address_line1;
                $city    = $this->city;
                $state   = $this->state_province;
                $postal  = $this->postal_code;
                $country = $this->country;
                $latitude = $this->latitude;
                $longitude = $this->longitude;
                $logo = $this->logo;
                $logo_mime_type = $this->logo_mime_type;
                $logo_filename = $this->logo_filename;
                $logo_charset = $this->logo_charset;
                $logo_last_updated = $this->logo_last_updated;

                $stmt->bind_param("issssssssssssss", 
                                    $id, 
                                    $name, 
                                    $web, 
                                    $address, 
                                    $city, 
                                    $state, 
                                    $postal, 
                                    $country, 
                                    $latitude, 
                                    $longitude, 
                                    $logo, 
                                    $logo_mime_type, 
                                    $logo_filename, 
                                    $logo_charset, 
                                    $logo_last_updated);
                $stmt->execute();

                if ($this->store_id === null) {
                    $this->store_id = $db->conn->insert_id;
                }

                $db->conn->commit(); // Confirmem la transacció
            } catch (\mysqli_sql_exception $e) {
                // Error de base de dades: desfem i deixem que l'error pugi
                // fins al qui ha cridat save() (per exemple, customers_list).
                if ($db && $db->conn)
                    $db->conn->rollback();
                throw $e;   // conserva codi, missatge i traça originals
            } catch (\Throwable $e) {
                // Error, Exception o ErrorException: també desfem, perquè la
                // transacció oberta per connectDB() no quedi penjada.
                if ($db && $db->conn)
                    $db->conn->rollback();
                throw $e;
            } finally {
                // closeDB() ja comprova si hi ha connexió
                if ($db)
                    $db->closeDB();
            }
        }
        
        public function destroy(){
            // $db es declara fora del try: si el constructor de Database falla,
            // catch i finally s'executen igualment i la variable no existiria.
            $db = null;
            try {
                // Connectar a la base de dades
                $db = new Database();
                $db->connectDB(BASE_PATH . '/config.db');

                // Obtenir el nom de la taula de la classe filla
                $table = static::$table;

                if ($this->store_id === null) {
                    throw new \Exception("Store ID is not set. Cannot delete the store.");
                }
                // Comprovem que el registre existeix
                $sql = "SELECT * FROM $table WHERE store_id = ?";
                $stmt = $db->conn->prepare($sql);
                $id = $this->store_id;
                $stmt->bind_param("i", $id);
                $stmt->execute();

                $result = $stmt->get_result();
                if ($result->num_rows === 0) {
                    throw new \Exception("Store with ID $id does not exist. Cannot delete.");
                }
                // Si la botiga té comandes o enviaments, la clau forana amb
                // RESTRICT farà fallar aquest DELETE amb l'error 1451.
                $sql = "DELETE FROM $table WHERE store_id = ?";
                $stmt = $db->conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();

                $db->conn->commit(); // Confirmem la transacció
            } catch (\mysqli_sql_exception $e) {
                // Error de base de dades: desfem i deixem que l'error pugi
                // fins al qui ha cridat save() (per exemple, customers_list).
                if ($db && $db->conn)
                    $db->conn->rollback();
                throw $e;   // conserva codi, missatge i traça originals
            } catch (\Throwable $e) {
                // Error, Exception o ErrorException: també desfem, perquè la
                // transacció oberta per connectDB() no quedi penjada.
                if ($db && $db->conn)
                    $db->conn->rollback();
                throw $e;
            } finally {
                // closeDB() ja comprova si hi ha connexió
                if ($db)
                    $db->closeDB();
            }
        }

        public static function find($id) {
            $db = null;
            try {
                $db = new Database();
                $db->connectDB(BASE_PATH . '/config.db');

                $table = static::$table;

                $sql = "SELECT * FROM $table WHERE store_id = ?";
                $stmt = $db->conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    return null; // No s'ha trobat cap registre amb aquest ID
                }

                $row = $result->fetch_assoc();
                return new static(...array_values($row));
            } catch (\Throwable $e) {
                throw $e;
            } finally {
                if ($db)
                    $db->closeDB();
            }
        }
    }
?>