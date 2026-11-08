<?php
    namespace models;
    use config\Database;
    class Customer extends Model{
        protected static string $table = 'customers';

        //Constructor
        // L'ordre dels paràmetres ha de coincidir EXACTAMENT amb l'ordre de les
        // columnes de la taula: Model::all() fa new static(...array_values($row)),
        // és a dir, passa els valors per posició.
        public function __construct(
            // ?int amb null per defecte: un client nou encara no té id
            public ?int    	$customer_id = null,
            public ?string 	$email_address = null,
            public ?string 	$first_name = null,
            public ?string 	$last_name = null,
            public ?string 	$phone_number = null,
            public ?string 	$address_line1 = null,
            public ?string 	$city = null,
            public ?string 	$state_province = null,
            public ?string 	$postal_code = null,
            public ?string 	$country = null,
            public ?string 	$created_at = null
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

                // email_address és UNIQUE. Sense aquesta comprovació, l'INSERT ...
                // ON DUPLICATE KEY UPDATE actualitzaria el CLIENT QUE JA TÉ
                // AQUELL EMAIL en lloc de rebutjar el nou.
                if ($this->email_address !== null && $this->email_address !== '') {
                    $sql = "SELECT customer_id FROM $table WHERE email_address = ? AND (? IS NULL OR customer_id <> ?) LIMIT 1";
                    $stmt = $db->conn->prepare($sql);
                    $id = $this->customer_id;
                    $stmt->bind_param("sii", $this->email_address, $id, $id);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $stmt->close();
                        throw new \DomainException("Ja existeix un client amb l'email {$this->email_address}.");
                    }
                    $stmt->close();
                }

                // Un sol INSERT: si la clau primària (o l'email, que és UNIQUE)
                // ja existeix, ON DUPLICATE KEY UPDATE actualitza la fila.
                $sql = "INSERT INTO $table (customer_id, email_address, first_name, last_name, phone_number, address_line1, city, state_province, postal_code, country)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        first_name = VALUES(first_name),
                        last_name = VALUES(last_name),
                        email_address = VALUES(email_address),
                        phone_number = VALUES(phone_number),
                        address_line1 = VALUES(address_line1),
                        city = VALUES(city),
                        state_province = VALUES(state_province),
                        postal_code = VALUES(postal_code),
                        country = VALUES(country)";
                $stmt = $db->conn->prepare($sql);

                // bind_param necessita variables per referència.
                // Si customer_id és null, enviem NULL i AUTO_INCREMENT fa la resta.
                $id      = $this->customer_id;
                $email   = $this->email_address;
                $first   = $this->first_name;
                $last    = $this->last_name;
                $phone   = $this->phone_number;
                $address = $this->address_line1;
                $city    = $this->city;
                $state   = $this->state_province;
                $postal  = $this->postal_code;
                $country = $this->country;

                $stmt->bind_param("isssssssss", $id, $email, $first, $last, $phone, $address, $city, $state, $postal, $country);
                $stmt->execute();

                // Si era un registre nou, recuperem l'id que ha generat la base de dades
                if ($this->customer_id === null) {
                    $this->customer_id = $db->conn->insert_id;
                }

                // Únic camí que tanca la transacció amb èxit
                $db->conn->commit();

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
            // $db es declara fora del try pel mateix motiu que a save()
            $db = null;
            try {
                // Connectar a la base de dades
                $db = new Database();
                $db->connectDB(BASE_PATH . '/config.db');

                // Obtenir el nom de la taula de la classe filla
                $table = static::$table;

                if ($this->customer_id === null) {
                    throw new \Exception("Customer ID is not set. Cannot delete the customer.");
                }

                // Comprovem que el registre existeix
                $sql = "SELECT * FROM $table WHERE customer_id = ?";
                $stmt = $db->conn->prepare($sql);
                $id = $this->customer_id;
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    throw new \Exception("El client no existeix.");
                }

                // Si el client té comandes o enviaments, la clau forana amb
                // RESTRICT farà fallar aquest DELETE amb l'error 1451.
                $sql = "DELETE FROM $table WHERE customer_id = ?";
                $stmt = $db->conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();

                // Únic camí que tanca la transacció amb èxit
                $db->conn->commit();

            } catch (\mysqli_sql_exception $e) {
                // Error de base de dades (per exemple, 1451: té dependències):
                // desfem i propaguem amb el codi intacte perquè el cridador
                // pugui distingir-ho i informar l'usuari.
                if ($db && $db->conn)
                    $db->conn->rollback();
                throw $e;
            } catch (\Throwable $e) {
                if ($db && $db->conn)
                    $db->conn->rollback();
                throw $e;
            } finally {
                if ($db)
                    $db->closeDB();
            }
        }
        public static function find(int $id): ?self{
            $customer = null;
            $db = null;
            try {
                $db = new Database();
                $db->connectDB(BASE_PATH . '/config.db');
                $table = static::$table;
                $sql= "SELECT * FROM $table WHERE customer_id = ? LIMIT 1";
                $stmt = $db->conn->prepare($sql);
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                if ($row) {
                    $customer = new self(...array_values($row));
                }
            } finally {
                if ($db) $db->closeDB();
            }
            return $customer;
        }
    }
?>