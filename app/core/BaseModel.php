<?php

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function findBy($column, $value) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1");
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function all($orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function where($conditions = [], $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $whereConditions = [];
            
            foreach ($conditions as $column => $value) {
                $whereConditions[] = "{$column} = :{$column}";
            }
            
            $sql .= implode(' AND ', $whereConditions);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($conditions as $column => $value) {
            $stmt->bindValue(":{$column}", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function create($data) {
        // Filter data based on fillable fields
        $filteredData = [];
        foreach ($this->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $filteredData[$field] = $data[$field];
            }
        }
        
        // Add timestamps if enabled
        if ($this->timestamps) {
            $filteredData['created_at'] = date('Y-m-d H:i:s');
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $columns = implode(', ', array_keys($filteredData));
        $placeholders = ':' . implode(', :', array_keys($filteredData));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        foreach ($filteredData as $column => $value) {
            $type = PDO::PARAM_STR;

            if ($value === null) {
                $type = PDO::PARAM_NULL;
            } elseif (is_int($value)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = PDO::PARAM_INT;
                $value = $value ? 1 : 0;
            }

            $stmt->bindValue(":{$column}", $value, $type);
        }
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        // Filter data based on fillable fields
        $filteredData = [];
        foreach ($this->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $filteredData[$field] = $data[$field];
            }
        }
        
        // Add updated timestamp if enabled
        if ($this->timestamps) {
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $setParts = [];
        foreach ($filteredData as $column => $value) {
            $setParts[] = "{$column} = :{$column}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        
        foreach ($filteredData as $column => $value) {
            $type = PDO::PARAM_STR;

            if ($value === null) {
                $type = PDO::PARAM_NULL;
            } elseif (is_int($value)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = PDO::PARAM_INT;
                $value = $value ? 1 : 0;
            }

            $stmt->bindValue(":{$column}", $value, $type);
        }
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $whereConditions = [];
            
            foreach ($conditions as $column => $value) {
                $whereConditions[] = "{$column} = :{$column}";
            }
            
            $sql .= implode(' AND ', $whereConditions);
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($conditions as $column => $value) {
            $stmt->bindValue(":{$column}", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    public function paginate($page = 1, $perPage = 10, $conditions = [], $orderBy = null) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $whereConditions = [];
            
            foreach ($conditions as $column => $value) {
                $whereConditions[] = "{$column} = :{$column}";
            }
            
            $sql .= implode(' AND ', $whereConditions);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($conditions as $column => $value) {
            $stmt->bindValue(":{$column}", $value);
        }
        
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        $total = $this->count($conditions);
        $totalPages = ceil($total / $perPage);
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ];
    }
}