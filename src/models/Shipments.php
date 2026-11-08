<?php
namespace models;
use config\Database;
class Shipments extends Model{
    protected static string $table = 'shipments';

    //Constructor
    // L'ordre dels paràmetres ha de coincidir EXACTAMENT amb l'ordre de les
    // columnes de la taula: Model::all() fa new static(...array_values($row)),
    // és a dir, passa els valors per posició.
    public function __construct(
        public ?int     $shipment_id = null,
        public ?int     $store_id = null,
        public ?int     $customer_id = null,
        public ?string  $address_line1 = null,
        public ?string  $city = null,
        public ?string  $state_province = null,
        public ?string  $postal_code = null,
        public ?string  $country = null,
        public ?string  $shipment_status = null
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

            $sql = "INSERT INTO $table (shipment_id, store_id, customer_id, address_line1, city, state_province, postal_code, country, shipment_status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        store_id = VALUES(store_id),
                        customer_id = VALUES(customer_id),
                        address_line1 = VALUES(address_line1),
                        city = VALUES(city),
                        state_province = VALUES(state_province),
                        postal_code = VALUES(postal_code),
                        country = VALUES(country),
                        shipment_status = VALUES(shipment_status)";

            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param(
                "iiissssss",
                $this->shipment_id,
                $this->store_id,
                $this->customer_id,
                $this->address_line1,
                $this->city,
                $this->state_province,
                $this->postal_code,
                $this->country,
                $this->shipment_status
            );
            $stmt->execute();

            if ($this->shipment_id === null) {
                // Si és un nou enviament, obtenir l'ID generat automàticament
                $this->shipment_id = (int) $stmt->insert_id;
            }

            $db->conn->commit();
        } catch (\mysqli_sql_exception $e) {
            if ($db && $db->conn) {
                $db->conn->rollback();
            }
            throw $e; // Re-llançar l'excepció per a que pugui ser capturada més amunt
        }catch (\Throwable $e) {
            if ($db && $db->conn) {
                $db->conn->rollback();
            }
            throw $e; // Re-llançar l'excepció per a que pugui ser capturada més amunt
        } finally {
            if ($db) {
                $db->closeDB();
            }
        }
    }

    public function destroy(): void {
        $db = null;
        try {
            $db = new Database();
            $db->connectDB(BASE_PATH . '/config.db');

            $table = static::$table;

            $sql = "SELECT * FROM $table WHERE shipment_id = ?";
            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param("i", $this->shipment_id);
            $stmt->execute();

            $result = $stmt->get_result();
            if($result->num_rows === 0){
                throw new \Exception("No s'ha trobat l'enviament amb ID: " . $this->shipment_id);
            }

            $sql = "DELETE FROM $table WHERE shipment_id = ?";
            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param("i", $this->shipment_id);
            $stmt->execute();

            $db->conn->commit();
        } catch (\mysqli_sql_exception $e) {
            if ($db && $db->conn) {
                $db->conn->rollback();
            }
            throw $e;
        } finally {
            if ($db) {
                $db->closeDB();
            }
        }
    }

    public static function find($id): ?Shipments {
        $db = null;
        try {
            $db = new Database();
            $db->connectDB(BASE_PATH . '/config.db');

            $table = static::$table;

            $sql = "SELECT * FROM $table WHERE shipment_id = ?";
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
            if ($db) {
                $db->closeDB();
            }
        }
    }

    /* Llistat amb els noms de botiga i client, que all() no pot donar perquè
       només fa SELECT * FROM shipments. Retorna files (arrays), no objectes:
       són per pintar, no per desar. */
    public static function detailed(?string $status = null, int $limit = 200): array {
        $db = null;
        try {
            $db = new Database();
            $db->connectDB(BASE_PATH . '/config.db');

            $sql = "SELECT s.shipment_id, s.store_id, s.customer_id, s.address_line1,
                           s.city, s.state_province, s.postal_code, s.country,
                           s.shipment_status,
                           st.store_name,
                           c.first_name, c.last_name, c.email_address
                    FROM shipments s
                    JOIN stores st   ON st.store_id    = s.store_id
                    JOIN customers c ON c.customer_id  = s.customer_id";

            if ($status !== null) {
                $sql .= " WHERE s.shipment_status = ?";
            }

            $sql .= " ORDER BY s.shipment_id DESC LIMIT ?";

            $stmt = $db->conn->prepare($sql);
            if ($status !== null) {
                $stmt->bind_param("si", $status, $limit);
            } else {
                $stmt->bind_param("i", $limit);
            }
            $stmt->execute();

            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            if ($db) {
                $db->closeDB();
            }
        }
    }

    /* Els darrers enviaments d'una botiga, amb el nom del client, per a la
       fitxa de botiga. Com detailed(), retorna files per pintar, no objectes. */
    public static function byStore(int $store_id, int $limit = 10): array {
        $db = null;
        try {
            $db = new Database();
            $db->connectDB(BASE_PATH . '/config.db');

            $sql = "SELECT s.shipment_id, s.city, s.state_province, s.shipment_status,
                           c.first_name, c.last_name
                    FROM shipments s
                    JOIN customers c ON c.customer_id = s.customer_id
                    WHERE s.store_id = ?
                    ORDER BY s.shipment_id DESC
                    LIMIT ?";

            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param("ii", $store_id, $limit);
            $stmt->execute();

            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            if ($db) {
                $db->closeDB();
            }
        }
    }

    /* Quants enviaments surten d'una botiga: el total no cap a la llista
       limitada, així que es compta a part. */
    public static function countByStore(int $store_id): int {
        $db = null;
        try {
            $db = new Database();
            $db->connectDB(BASE_PATH . '/config.db');

            $stmt = $db->conn->prepare("SELECT COUNT(*) AS total FROM shipments WHERE store_id = ?");
            $stmt->bind_param("i", $store_id);
            $stmt->execute();

            $row = $stmt->get_result()->fetch_assoc();

            return (int) $row['total'];
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            if ($db) {
                $db->closeDB();
            }
        }
    }

    /* Quants enviaments hi ha de cada estat, per als comptadors del filtre. */
    public static function countByStatus(): array {
        $db = null;
        try {
            $db = new Database();
            $db->connectDB(BASE_PATH . '/config.db');

            $result = $db->conn->query("SELECT shipment_status, COUNT(*) AS total
                                        FROM shipments
                                        GROUP BY shipment_status");

            $counts = [];
            while ($row = $result->fetch_assoc()) {
                $counts[$row['shipment_status']] = (int) $row['total'];
            }

            return $counts;
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            if ($db) {
                $db->closeDB();
            }
        }
    }
}