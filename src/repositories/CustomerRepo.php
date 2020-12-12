<?php

namespace Wulff\repositories;

use PDO;
use PDOException;
use Wulff\config\Database;

class CustomerRepo
{
    private Database $db;

    function __construct($db)
    {
        $this->db = $db;
    }

    public function closeConnection()
    {
        $this->db->close();
    }

    public function find($id)
    {
        $query = <<<'SQL'
                SELECT *
                FROM customer
                WHERE CustomerId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $customer = $stmt->fetch();

        return $customer;
    }

    public function updateCustomer(int $id, array $customer): bool
    {
        return $this->db->update(
            'customer',
            'CustomerId',
            $id,
            (array)$customer
        );
    }

    public function deleteCustomer(int $id): bool
    {
        $query = <<<SQL
            DELETE FROM customer WHERE CustomerId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        try {
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // returns array ['total' => value (int)]
    public function calculateTracksTotal(array $tracks): array
    {
        $query = 'SELECT SUM(UnitPrice) as total FROM track WHERE TrackId in (';
        $isFirst = true;
        $params = array();
        foreach ($tracks as $track) {
            if ($isFirst) {
                $isFirst = false;
                $query .= '?';
            } else {
                $query .= ', ?';
            }
        }

        $query .= ");";

        $stmt = $this->db->conn->prepare($query);
        $stmt->execute($tracks);
        return $stmt->fetch();
    }

    public function createCustomerInvoice(array $tracks, array $data): int
    {
        $invoiceId = -1;
        $this->db->conn->beginTransaction();

        try {
            // insert invoice
            $invoiceId = $this->db->insert('invoice', $data);
        } catch (PDOException $e) {
            // something went wrong
            $this->db->conn->rollBack();
            $invoiceId = -1;
            return $invoiceId;
        }

        // insert invoice lines
        // get track ids with prices
        $tracks = $this->getTrackIdWithUnitPrice($tracks);

        foreach ($tracks as $key => $value) {
            // insert invoice line for track
            $invoiceLine = [
                'InvoiceId' => $invoiceId,
                'Quantity' => 1,
                'TrackId' => $value['TrackId'],
                'UnitPrice' => $value['UnitPrice']
            ];

            try {
                $this->db->insert('InvoiceLine', $invoiceLine);
            } catch (PDOException $e) {
                //
                $invoiceId = -1;
                return $invoiceId;
            }
        }

        try {
            // commit changes
            $this->db->conn->commit();
        } catch (PDOException $e) {
            $invoiceId = -1;
            return $invoiceId;
        }

        // invoice and invoicelines added successfully
        return $invoiceId;
    }

    /**
     * @param array $trackIds
     * @return array [[0] => [TrackId] => value], [[1] => [TrackId] => value]]
     */
    public function getTrackIdWithUnitPrice(array $trackIds): array
    {
        $query = <<<SQL
            SELECT TrackId, UnitPrice FROM track WHERE TrackId in (
SQL;
        $isFirst = true;
        $idPlaceholders = '';

        foreach ($trackIds as $id) {
            if ($isFirst) {
                $isFirst = false;
                $idPlaceholders .= '?';
            } else {
                $idPlaceholders .= ', ?';
            }
        }

        $idPlaceholders .= ');';
        $query .= $idPlaceholders;

        $stmt = $this->db->conn->prepare($query);
        $stmt->execute($trackIds);
        return $stmt->fetchAll();
    }

    public function findInvoice(int $id): ?array
    {
        $query = <<<SQL
            SELECT * FROM invoice WHERE InvoiceId = :id;
SQL;
        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findInvoicelinesByInvoiceId(int $id): ?array
    {
        $query = <<<SQL
            SELECT * FROM invoiceline WHERE InvoiceId = :id;
SQL;
        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}