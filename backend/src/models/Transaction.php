<?php
// src/models/Transaction.php
// Handles transaction-related database operations

class Transaction
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create($user_id, $amount, $description)
    {
        $stmt = $this->db->prepare('INSERT INTO transactions (user_id, amount, description, created_at) VALUES (?, ?, ?, NOW())');
        return $stmt->execute([$user_id, $amount, $description]);
    }

    public function getByUser($user_id)
    {
        $stmt = $this->db->prepare('SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add more transaction-related methods as needed
}
