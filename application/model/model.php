<?php

class Model
{
    /**
     * @param object $db A PDO database connection
     */
    function __construct($db)
    {
        try {
            $this->db = $db;
        } catch (PDOException $e) {
            exit('Database connection could not be established.');
        }
    }
    ///////USERS & LOGINS FUnctions//////////////////////////
    /**
     * Get staff member by email (basic method without joins)
     * @param string $email Staff email address
     * @return object|false Staff object or false if not found
     */
    public function getStaff($email)
    {
        $sql = "SELECT id, email, password, role, country_id, state_id, department_id, created_at, updated_at FROM staff_login WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Get staff member with location AND department details by email
     * @param string $email Staff email address
     * @return object|false Staff object with location and department info or false if not found
     */
    public function getStaffWithLocation($email)
    {
        $sql = "SELECT 
                    s.id,
                    s.email,
                    s.password,
                    s.role,
                    s.country_id,
                    s.state_id,
                    s.department_id,
                    s.created_at,
                    s.updated_at,
                    c.name as country_name, 
                    c.code as country_code, 
                    st.name as state_name, 
                    st.code as state_code,
                    d.name as department_name
                FROM staff_login s
                LEFT JOIN country c ON s.country_id = c.id
                LEFT JOIN state st ON s.state_id = st.id
                LEFT JOIN departments d ON s.department_id = d.id
                WHERE s.email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
        
    /**
     * Get all staff members with their location details
     * @return array Array of staff objects with location info
     */
    public function getAllStaff()
    {
        $sql = "SELECT 
                    s.id,
                    s.email,
                    s.password,
                    s.role,
                    s.country_id,
                    s.state_id,
                    s.department_id,
                    s.created_at,
                    s.updated_at,
                    c.name as country_name, 
                    c.code as country_code, 
                    st.name as state_name, 
                    st.code as state_code,
                    d.name as department_name
                FROM staff_login s
                LEFT JOIN country c ON s.country_id = c.id
                LEFT JOIN state st ON s.state_id = st.id
                LEFT JOIN departments d ON s.department_id = d.id
                ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Create a new staff member with department
     * @param array $data Staff data (email, password, role, country_id, state_id, department_id)
     * @return bool True on success, false on failure
     */
    public function createStaff($data)
    {
        $sql = "INSERT INTO staff_login (email, password, role, country_id, state_id, department_id) 
                VALUES (:email, :password, :role, :country_id, :state_id, :department_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':email' => $data['email'],
            ':password' => $data['password'],
            ':role' => $data['role'],
            ':country_id' => $data['country_id'] ?? null,
            ':state_id' => $data['state_id'] ?? null,
            ':department_id' => $data['department_id'] ?? null
        ]);
    }
    
    /**
     * Update staff member with department
     * @param int $id Staff ID
     * @param array $data Updated staff data
     * @return bool True on success, false on failure
     */
    public function updateStaff($id, $data)
    {
        $sql = "UPDATE staff_login 
                SET email = :email, 
                    role = :role, 
                    country_id = :country_id, 
                    state_id = :state_id,
                    department_id = :department_id,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':email' => $data['email'],
            ':role' => $data['role'],
            ':country_id' => $data['country_id'] ?? null,
            ':state_id' => $data['state_id'] ?? null,
            ':department_id' => $data['department_id'] ?? null
        ]);
    }
    
    /**
     * Update staff location
     * @param string $email Staff email
     * @param int|null $country_id Country ID
     * @param int|null $state_id State ID
     * @return bool True on success, false on failure
     */
    public function updateStaffLocation($email, $country_id, $state_id)
    {
        $sql = "UPDATE staff_login SET country_id = :country_id, state_id = :state_id, updated_at = CURRENT_TIMESTAMP WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':email' => $email,
            ':country_id' => $country_id,
            ':state_id' => $state_id
        ]);
    }
    
    /**
     * Reset staff password
     * @param string $email Staff email
     * @param string $hashed_password New hashed password
     * @return bool True on success, false on failure
     */
    public function reset_password($email, $hashed_password)
    {
        $sql = "UPDATE staff_login SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password' => $hashed_password,
            ':email' => $email
        ]);
    }
    
    /**
     * Delete staff member
     * @param int $id Staff ID
     * @return bool True on success, false on failure
     */
    public function deleteStaff($id)
    {
        $sql = "DELETE FROM staff_login WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Get user profile by email (for logged in user)
    public function getUserProfileByEmail($email) {
        $sql = "SELECT sl.*, d.name as department_name, c.name as country_name, s.name as state_name
                FROM staff_login sl
                LEFT JOIN departments d ON sl.department_id = d.id
                LEFT JOIN country c ON sl.country_id = c.id
                LEFT JOIN state s ON sl.state_id = s.id
                WHERE sl.email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    ////////////location functions//////////////////////

    /**
     * Get all countries
     * @return array Array of country objects
     */
    public function getAllCountries()
    {
        $sql = "SELECT c.*, COUNT(s.id) as state_count 
                FROM country c
                LEFT JOIN state s ON c.id = s.country_id
                GROUP BY c.id
                ORDER BY c.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get country by ID
     * @param int $id Country ID
     * @return object|false Country object or false if not found
     */
    public function getCountryById($id)
    {
        $sql = "SELECT * FROM country WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Get country by code
     * @param string $code Country code (e.g., 'NGA', 'LBR', 'CMR')
     * @return object|false Country object or false if not found
     */
    public function getCountryByCode($code)
    {
        $sql = "SELECT * FROM country WHERE code = :code";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $code]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Create a new country
     * @param string $name Country name
     * @param string $code Country code (3 letters)
     * @return bool True on success, false on failure
     */
    public function createCountry($name, $code)
    {
        $sql = "INSERT INTO country (name, code) VALUES (:name, :code)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':code' => strtoupper($code)
        ]);
    }
    
    /**
     * Get all states for a specific country
     * @param int $country_id Country ID
     * @return array Array of state objects
     */
    public function getStatesByCountry($country_id)
    {
        $sql = "SELECT * FROM state WHERE country_id = :country_id ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':country_id' => $country_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get state by ID
     * @param int $id State ID
     * @return object|false State object or false if not found
     */
    public function getStateById($id)
    {
        $sql = "SELECT * FROM state WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Get state with country information
     * @param int $id State ID
     * @return object|false State object with country info or false if not found
     */
    public function getStateWithCountry($id)
    {
        $sql = "SELECT s.*, c.name as country_name, c.code as country_code 
                FROM state s
                LEFT JOIN country c ON s.country_id = c.id
                WHERE s.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Create a new state
     * @param int $country_id Country ID
     * @param string $name State name
     * @param string|null $code State code (optional)
     * @return bool True on success, false on failure
     */
    public function createState($country_id, $name, $code = null)
    {
        // Use INSERT IGNORE to skip duplicates instead of throwing error
        $sql = "INSERT IGNORE INTO state (country_id, name, code) VALUES (:country_id, :name, :code)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':country_id' => $country_id,
            ':name' => $name,
            ':code' => $code
        ]);
    }
    
    /**
     * Update state information
     * @param int $id State ID
     * @param string $name State name
     * @param string|null $code State code
     * @return bool True on success, false on failure
     */
    public function updateState($id, $name, $code = null)
    {
        $sql = "UPDATE state SET name = :name, code = :code WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':code' => $code
        ]);
    }
    
    /**
     * Delete state
     * @param int $id State ID
     * @return bool True on success, false on failure
     */
    public function deleteState($id)
    {
        $sql = "DELETE FROM state WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Get staff count by role
     * @param string $role Role name
     * @return int Number of staff with that role
     */
    public function getStaffCountByRole($role)
    {
        $sql = "SELECT COUNT(*) as count FROM staff_login WHERE role = :role";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':role' => $role]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->count : 0;
    }
    
    /**
     * Get staff members by country
     * @param int $country_id Country ID
     * @return array Array of staff objects
     */
    public function getStaffByCountry($country_id)
    {
        $sql = "SELECT s.*, st.name as state_name 
                FROM staff_login s
                LEFT JOIN state st ON s.state_id = st.id
                WHERE s.country_id = :country_id
                ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':country_id' => $country_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get staff members by state
     * @param int $state_id State ID
     * @return array Array of staff objects
     */
    public function getStaffByState($state_id)
    {
        $sql = "SELECT s.*, c.name as country_name 
                FROM staff_login s
                LEFT JOIN country c ON s.country_id = c.id
                WHERE s.state_id = :state_id
                ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':state_id' => $state_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    //////////////department functions////////////////
    /**
     * Get staff members by department
     * @param int $department_id Department ID
     * @return array Array of staff objects
     */
    public function getStaffByDepartment($department_id)
    {
        $sql = "SELECT s.*, c.name as country_name, st.name as state_name 
                FROM staff_login s
                LEFT JOIN country c ON s.country_id = c.id
                LEFT JOIN state st ON s.state_id = st.id
                WHERE s.department_id = :department_id
                ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':department_id' => $department_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Check if email already exists
     * @param string $email Email to check
     * @return bool True if exists, false otherwise
     */
    public function emailExists($email)
    {
        $sql = "SELECT COUNT(*) as count FROM staff_login WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count > 0;
    }
    
    /**
     * Get staff by ID
     * @param int $id Staff ID
     * @return object|false Staff object or false if not found
     */
    public function getStaffById($id)
    {
        $sql = "SELECT * FROM staff_login WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Get all departments
     */
    public function getAll($page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        $table = 'departments';
        
        $sql = "SELECT * FROM {$table} ORDER BY name LIMIT :limit OFFSET :offset";
        $query = $this->db->prepare($sql);
        $query->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->execute();
        
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get total number of departments
     */
    public function getTotalCount()
    {
        $table = 'departments';
        $sql = "SELECT COUNT(*) as total FROM {$table}";
        $query = $this->db->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        return $result->total;
    }

    /**
     * Get single department by ID
     */
    public function getById($id)
    {
        $table = 'departments';
        $sql = "SELECT * FROM {$table} WHERE id = :id LIMIT 1";
        $query = $this->db->prepare($sql);
        $query->execute([':id' => $id]);
        
        return $query->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Create new department
     */
    public function create($data)
    {
        $table = 'departments';
        $sql = "INSERT INTO {$table} (name) VALUES (:name)";
        $query = $this->db->prepare($sql);
        
        return $query->execute([
            ':name' => $data['name']
        ]);
    }

    /**
     * Update department
     */
    public function update($id, $data)
    {
        $table = 'departments';
        $sql = "UPDATE {$table} SET name = :name WHERE id = :id";
        $query = $this->db->prepare($sql);
        
        return $query->execute([
            ':id' => $id,
            ':name' => $data['name']
        ]);
    }

    /**
     * Delete department
     */
    public function delete($id)
    {
        $table = 'departments';
        $sql = "DELETE FROM {$table} WHERE id = :id";
        $query = $this->db->prepare($sql);
        
        return $query->execute([':id' => $id]);
    }

    /**
     * Check if department name exists
     */
    public function nameExists($name, $excludeId = null)
    {
        $table = 'departments';
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE name = :name";
        
        if ($excludeId) {
            $sql .= " AND id != :id";
        }
        
        $query = $this->db->prepare($sql);
        $params = [':name' => $name];
        
        if ($excludeId) {
            $params[':id'] = $excludeId;
        }
        
        $query->execute($params);
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        return $result->count > 0;
    }

    /**
     * Get all departments (alias for getAll method)
     */
    public function getAllDepartments()
    {
        return $this->getAll(1, 1000);
    }

    //////////////funder codes functions
    /**
     * Get all funder codes
     * @return array Array of funder code objects
     */
    public function getAllFunders()
    {
        $sql = "SELECT * FROM funder_codes ORDER BY name ASC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get a single funder code by ID
     * @param int $id Funder code ID
     * @return object|false Funder code object or false if not found
     */
    public function getFunderById($id)
    {
        $sql = "SELECT * FROM funder_codes WHERE id = :id LIMIT 1";
        $query = $this->db->prepare($sql);
        $params = array(':id' => $id);
        $query->execute($params);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Create a new funder code
     * @param string $name Funder code name
     * @return bool True on success, false on failure
     */
    public function createFunder($name)
    {
        $sql = "INSERT INTO funder_codes (name) VALUES (:name)";
        $query = $this->db->prepare($sql);
        $params = array(':name' => $name);
        return $query->execute($params);
    }

    /**
     * Update an existing funder code
     * @param int $id Funder code ID
     * @param string $name New funder code name
     * @return bool True on success, false on failure
     */
    public function updateFunder($id, $name)
    {
        $sql = "UPDATE funder_codes SET name = :name WHERE id = :id";
        $query = $this->db->prepare($sql);
        $params = array(':name' => $name, ':id' => $id);
        return $query->execute($params);
    }

    /**
     * Delete a funder code
     * @param int $id Funder code ID
     * @return bool True on success, false on failure
     */
    public function deleteFunder($id)
    {
        $sql = "DELETE FROM funder_codes WHERE id = :id";
        $query = $this->db->prepare($sql);
        $params = array(':id' => $id);
        return $query->execute($params);
    }

    /**
     * Check if funder code name already exists
     * @param string $name Funder code name to check
     * @param int|null $excludeId ID to exclude from check (for updates)
     * @return bool True if exists, false if not
     */
    public function funderNameExists($name, $excludeId = null)
    {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) FROM funder_codes WHERE name = :name AND id != :excludeId";
            $params = array(':name' => $name, ':excludeId' => $excludeId);
        } else {
            $sql = "SELECT COUNT(*) FROM funder_codes WHERE name = :name";
            $params = array(':name' => $name);
        }
        
        $query = $this->db->prepare($sql);
        $query->execute($params);
        return $query->fetchColumn() > 0;
    }

    /**
     * Get total count of funder codes
     * @return int Total number of funder codes
     */
    public function getTotalFunders()
    {
        $sql = "SELECT COUNT(*) FROM funder_codes";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchColumn();
    }

    /**
     * Search funder codes by name
     * @param string $search Search term
     * @return array Array of matching funder code objects
     */
    public function searchFunders($search)
    {
        $sql = "SELECT * FROM funder_codes WHERE name LIKE :search ORDER BY name ASC";
        $query = $this->db->prepare($sql);
        $params = array(':search' => '%' . $search . '%');
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /////////////////////////////////Airline Functions
    /**
     * Get all airlines
     * @return array Array of airline objects
     */
    public function getAllAirlines()
    {
        $sql = "SELECT * FROM airlines ORDER BY name ASC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get a single airline by ID
     * @param int $id Airline ID
     * @return object|false Airline object or false if not found
     */
    public function getAirlineById($id)
    {
        $sql = "SELECT * FROM airlines WHERE id = :id LIMIT 1";
        $query = $this->db->prepare($sql);
        $params = array(':id' => $id);
        $query->execute($params);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Create a new airline
     * @param string $name Airline name
     * @return bool True on success, false on failure
     */
    public function createAirline($name)
    {
        $sql = "INSERT INTO airlines (name) VALUES (:name)";
        $query = $this->db->prepare($sql);
        $params = array(':name' => $name);
        return $query->execute($params);
    }

    /**
     * Update an existing airline
     * @param int $id Airline ID
     * @param string $name New airline name
     * @return bool True on success, false on failure
     */
    public function updateAirline($id, $name)
    {
        $sql = "UPDATE airlines SET name = :name WHERE id = :id";
        $query = $this->db->prepare($sql);
        $params = array(':name' => $name, ':id' => $id);
        return $query->execute($params);
    }

    /**
     * Delete an airline
     * @param int $id Airline ID
     * @return bool True on success, false on failure
     */
    public function deleteAirline($id)
    {
        $sql = "DELETE FROM airlines WHERE id = :id";
        $query = $this->db->prepare($sql);
        $params = array(':id' => $id);
        return $query->execute($params);
    }

    /**
     * Check if airline name already exists
     * @param string $name Airline name to check
     * @param int|null $excludeId ID to exclude from check (for updates)
     * @return bool True if exists, false if not
     */
    public function airlineNameExists($name, $excludeId = null)
    {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) FROM airlines WHERE name = :name AND id != :excludeId";
            $params = array(':name' => $name, ':excludeId' => $excludeId);
        } else {
            $sql = "SELECT COUNT(*) FROM airlines WHERE name = :name";
            $params = array(':name' => $name);
        }
        
        $query = $this->db->prepare($sql);
        $query->execute($params);
        return $query->fetchColumn() > 0;
    }

    /**
     * Get total count of airlines
     * @return int Total number of airlines
     */
    public function getTotalAirlines()
    {
        $sql = "SELECT COUNT(*) FROM airlines";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchColumn();
    }

    /**
     * Search airlines by name
     * @param string $search Search term
     * @return array Array of matching airline objects
     */
    public function searchAirlines($search)
    {
        $sql = "SELECT * FROM airlines WHERE name LIKE :search ORDER BY name ASC";
        $query = $this->db->prepare($sql);
        $params = array(':search' => '%' . $search . '%');
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /////////////////hotel vendors functipons

    // Get all hotels with their state names
    public function getHotelsWithStates() {
        $sql = "SELECT 
                    h.*, 
                    s.name as state_name, 
                    s.code as state_code
                FROM hotel h
                JOIN state s ON h.state_id = s.id
                ORDER BY h.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get all states
    public function getAllStates() {
        $sql = "SELECT * FROM state ORDER BY name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get hotels by state ID
    public function getHotelsByState($state_id) {

        $sql = "SELECT *
                FROM hotel
                WHERE state_id = ?
                ORDER BY name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$state_id]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Add new hotel
    public function addHotel($name, $location, $state_id) {

        $sql = "INSERT INTO hotel (
                    name,
                    location,
                    state_id
                ) VALUES (
                    :name,
                    :location,
                    :state_id
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':name' => $name,
            ':location' => $location,
            ':state_id' => $state_id
        ]);
    }

    // Update hotel
    public function updateHotel($id, $name, $location, $state_id) {

        $sql = "UPDATE hotel
                SET
                    name = :name,
                    location = :location,
                    state_id = :state_id
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':location' => $location,
            ':state_id' => $state_id
        ]);
    }

    // Get single hotel
    public function getHotelById($id) {

        $sql = "SELECT * FROM hotel WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Delete hotel
    public function deleteHotel($id) {

        $sql = "DELETE FROM hotel WHERE id = ?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$id]);
    }

    //////driver details functions
    // Get all drivers
    public function getAllDrivers() {
        $sql = "SELECT *, SUBSTRING_INDEX(email, '@', 1) as driver_name FROM driver ORDER BY email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get driver by ID
    public function getDriverById($id) {
        $sql = "SELECT *, SUBSTRING_INDEX(email, '@', 1) as driver_name FROM driver WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get database connection
     */
    public function getDb()
    {
        return $this->db;
    }

    // Add new driver
    public function addDriver($email, $phone) {
        $sql = "INSERT INTO driver (email, phone) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$email, $phone]);
    }

    // Update driver
    public function updateDriver($id, $email, $phone) {
        $sql = "UPDATE driver SET email = ?, phone = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$email, $phone, $id]);
    }

    // Delete driver
    public function deleteDriver($id) {
        $sql = "DELETE FROM driver WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Check if email exists
    public function driverEmailExists($email, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM driver WHERE email = ? AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM driver WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
        }
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count > 0;
    }

    // Search drivers by email or phone
    public function searchDrivers($keyword) {
        $sql = "SELECT *, SUBSTRING_INDEX(email, '@', 1) as driver_name FROM driver WHERE email LIKE ? OR phone LIKE ? ORDER BY email";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%$keyword%";
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    ///////////////EA States functions

    // Get all EA states 
    public function getAllEaStates() {
        $sql = "SELECT es.*, 
                s.name as state_name, s.code as state_code,
                c.name as country_name,
                d.email as driver_email, d.phone as driver_phone,
                CONCAT(SUBSTRING_INDEX(es.reviewer_email, '@', 1)) as reviewer_name,
                CONCAT(SUBSTRING_INDEX(es.co_reviewer_email, '@', 1)) as co_reviewer_name,
                CONCAT(SUBSTRING_INDEX(es.manager_email, '@', 1)) as manager_name,
                CONCAT(SUBSTRING_INDEX(es.security_manager_email, '@', 1)) as security_manager_name
                FROM ea_state es
                JOIN state s ON es.state_id = s.id
                JOIN country c ON s.country_id = c.id
                LEFT JOIN driver d ON es.driver_id = d.id
                ORDER BY c.name, s.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Add new EA state 
    public function addEaState($state_id, $reviewer_email, $co_reviewer_email, $manager_email, 
                            $security_manager_email = null, $driver_id = null) {
        $sql = "INSERT INTO ea_state (state_id, reviewer_email, co_reviewer_email, manager_email, 
                                    security_manager_email, driver_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$state_id, $reviewer_email, $co_reviewer_email, $manager_email, 
                            $security_manager_email, $driver_id]);
    }

    // Update EA state 
    public function updateEaState($id, $state_id, $reviewer_email, $co_reviewer_email, $manager_email,
                                $security_manager_email = null, $driver_id = null) {
        $sql = "UPDATE ea_state SET state_id = ?, reviewer_email = ?, co_reviewer_email = ?, 
                manager_email = ?, security_manager_email = ?, driver_id = ? 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$state_id, $reviewer_email, $co_reviewer_email, $manager_email,
                            $security_manager_email, $driver_id, $id]);
    }


    // Get EA state by ID (UPDATED with security and operations managers)
    public function getEaStateById($id) {
        $sql = "SELECT es.*, 
                s.name as state_name, s.code as state_code, s.country_id,
                c.name as country_name,
                d.email as driver_email, d.phone as driver_phone
                FROM ea_state es
                JOIN state s ON es.state_id = s.id
                JOIN country c ON s.country_id = c.id
                LEFT JOIN driver d ON es.driver_id = d.id
                WHERE es.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Get EA state by state_id (UPDATED with security and operations managers)
    public function getEaStateByStateId($state_id) {
        $sql = "SELECT * FROM ea_state WHERE state_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$state_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Delete EA state
    public function deleteEaState($id) {
        $sql = "DELETE FROM ea_state WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Get available states (not yet assigned to ea_state)
    public function getAvailableStates() {
        $sql = "SELECT s.*, c.name as country_name 
                FROM state s
                JOIN country c ON s.country_id = c.id
                WHERE s.id NOT IN (SELECT state_id FROM ea_state)
                ORDER BY c.name, s.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get staff emails by role (for dropdowns)
    public function getStaffEmailsByRole($role = null) {
        if ($role) {
            $sql = "SELECT email, role FROM staff_login WHERE role = ? ORDER BY email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$role]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } else {
            $sql = "SELECT email, role FROM staff_login ORDER BY email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        }
    }

    // Get all staff emails (for reviewers and managers)
    public function getAllStaffEmails() {
        $sql = "SELECT email, role, CONCAT(SUBSTRING_INDEX(email, '@', 1)) as name 
                FROM staff_login 
                WHERE role IN ('admin', 'super_admin', 'staff') 
                ORDER BY email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get security managers (admin and super_admin users)
    public function getSecurityManagers() {
        $sql = "SELECT email, role, CONCAT(SUBSTRING_INDEX(email, '@', 1)) as name 
                FROM staff_login 
                WHERE role IN ('admin', 'super_admin') 
                ORDER BY email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get all drivers for dropdown
    public function getAllDriversForDropdown() {
        $sql = "SELECT id, email, phone, SUBSTRING_INDEX(email, '@', 1) as name 
                FROM driver 
                ORDER BY email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Check if state already has EA configuration
    public function eaStateExists($state_id, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM ea_state WHERE state_id = ? AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$state_id, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM ea_state WHERE state_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$state_id]);
        }
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count > 0;
    }

    ///////////////////////////////////////////////////////////////////////////
    ////////////intrastate(within the state) requests functions
    // Get all intrastate requests
    public function getAllIntrastateRequests() {
        $sql = "SELECT ir.*,
                CONCAT(SUBSTRING_INDEX(ir.staff_email, '@', 1)) as staff_name,
                CONCAT(SUBSTRING_INDEX(ir.supervisor_email, '@', 1)) as supervisor_name,
                s.name as vehicle_location_state_name, s.code as vehicle_location_code,
                fc.name as funder_code_name,
                CONCAT(SUBSTRING_INDEX(ir.reviewer_email, '@', 1)) as reviewer_name,
                CONCAT(SUBSTRING_INDEX(ir.co_reviewer_email, '@', 1)) as co_reviewer_name,
                CONCAT(SUBSTRING_INDEX(ir.manager_email, '@', 1)) as manager_name,
                CONCAT(SUBSTRING_INDEX(ir.security_manager_email, '@', 1)) as security_manager_name,
                CONCAT(SUBSTRING_INDEX(ir.overtime_manager_email, '@', 1)) as overtime_manager_name,
                d.email as driver_email, d.phone as driver_phone,
                CONCAT(SUBSTRING_INDEX(d.email, '@', 1)) as driver_name
                FROM intrastate_request ir
                JOIN state s ON ir.vehicle_location_state_id = s.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                LEFT JOIN driver d ON ir.assigned_driver_id = d.id
                ORDER BY ir.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get intrastate request by ID
    public function getIntrastateRequestById($id) {
        $sql = "SELECT ir.*,
                CONCAT(SUBSTRING_INDEX(ir.staff_email, '@', 1)) as staff_name,
                s.name as vehicle_location_state_name, s.code as vehicle_location_code,
                fc.name as funder_code_name,
                CONCAT(SUBSTRING_INDEX(ir.reviewer_email, '@', 1)) as reviewer_name,
                CONCAT(SUBSTRING_INDEX(ir.co_reviewer_email, '@', 1)) as co_reviewer_name,
                CONCAT(SUBSTRING_INDEX(ir.manager_email, '@', 1)) as manager_name,
                CONCAT(SUBSTRING_INDEX(ir.security_manager_email, '@', 1)) as security_manager_name,
                CONCAT(SUBSTRING_INDEX(ir.overtime_manager_email, '@', 1)) as overtime_manager_name,
                d.email as driver_email, d.phone as driver_phone,
                CONCAT(SUBSTRING_INDEX(d.email, '@', 1)) as driver_name
                FROM intrastate_request ir
                JOIN state s ON ir.vehicle_location_state_id = s.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                LEFT JOIN driver d ON ir.assigned_driver_id = d.id
                WHERE ir.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Get intrastate requests by staff email
    public function getIntrastateRequestsByStaff($staff_email) {
        $sql = "SELECT ir.*,
                s.name as vehicle_location_state_name,
                fc.name as funder_code_name,
                CASE 
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'reviewer' THEN 'Waiting for Reviewer'
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'co_reviewer' THEN 'Waiting for Co-Reviewer'
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'manager' THEN 'Waiting for Manager'
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'security_manager' THEN 'Waiting for Security Manager'
                    WHEN ir.status = 'rejected' THEN 'Rejected'
                    WHEN ir.status = 'security_approved' THEN 'Approved - Awaiting Driver Assignment'
                    WHEN ir.status = 'completed' THEN 'Completed'
                    WHEN ir.status = 'cancelled' THEN 'Cancelled'
                    ELSE REPLACE(ir.status, '_', ' ')
                END as approval_status_text
                FROM intrastate_request ir
                JOIN state s ON ir.vehicle_location_state_id = s.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                WHERE ir.staff_email = ?
                ORDER BY ir.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$staff_email]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get intrastate requests pending for specific approver
    public function getIntrastateRequestsByApprover($approver_email, $level) {
        $sql = "SELECT ir.*,
                s.name as vehicle_location_state_name,
                fc.name as funder_code_name,
                CONCAT(SUBSTRING_INDEX(ir.staff_email, '@', 1)) as staff_name,
                ir.staff_phone
                FROM intrastate_request ir
                JOIN state s ON ir.vehicle_location_state_id = s.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                WHERE ir.status = 'pending' AND ir.current_approval_level = ?";
        
        if ($level == 'reviewer') {
            $sql .= " AND ir.reviewer_email = ?";
        } elseif ($level == 'co_reviewer') {
            $sql .= " AND ir.co_reviewer_email = ?";
        } elseif ($level == 'manager') {
            $sql .= " AND ir.manager_email = ?";
        } elseif ($level == 'security_manager') {
            $sql .= " AND ir.security_manager_email = ?";
        }
        
        $sql .= " ORDER BY ir.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$level, $approver_email]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Create new intrastate request
    public function createIntrastateRequest($data) {
        $sql = "INSERT INTO intrastate_request (
            staff_email, staff_phone, supervisor_email, vehicle_location_state_id,
            reviewer_email, co_reviewer_email, manager_email, security_manager_email,
            trip_date, return_date, total_nights, purpose, pickup_location,
            trip_destination, trip_destination_time, route_information, funder_code_id,
            driver_overtime, trip_activity, reason_for_overtime, overtime_manager_email,
            need_driver_pickup, pickup_time, status, current_approval_level
        ) VALUES (
            :staff_email, :staff_phone, :supervisor_email, :vehicle_location_state_id,
            :reviewer_email, :co_reviewer_email, :manager_email, :security_manager_email,
            :trip_date, :return_date, :total_nights, :purpose, :pickup_location,
            :trip_destination, :trip_destination_time, :route_information, :funder_code_id,
            :driver_overtime, :trip_activity, :reason_for_overtime, :overtime_manager_email,
            :need_driver_pickup, :pickup_time, 'pending', 'reviewer'
        )";
        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            ':staff_email' => $data['staff_email'],
            ':staff_phone' => $data['staff_phone'],
            ':supervisor_email' => $data['supervisor_email'],
            ':vehicle_location_state_id' => $data['vehicle_location_state_id'],
            ':reviewer_email' => $data['reviewer_email'],
            ':co_reviewer_email' => $data['co_reviewer_email'],
            ':manager_email' => $data['manager_email'],
            ':security_manager_email' => $data['security_manager_email'],
            ':trip_date' => $data['trip_date'],
            ':return_date' => $data['return_date'],
            ':total_nights' => $data['total_nights'],
            ':purpose' => $data['purpose'],
            ':pickup_location' => $data['pickup_location'],
            ':trip_destination' => $data['trip_destination'],
            ':trip_destination_time' => $data['trip_destination_time'],
            ':route_information' => $data['route_information'],
            ':funder_code_id' => $data['funder_code_id'],
            ':driver_overtime' => $data['driver_overtime'],
            ':trip_activity' => $data['trip_activity'],
            ':reason_for_overtime' => $data['reason_for_overtime'],
            ':overtime_manager_email' => $data['overtime_manager_email'],
            ':need_driver_pickup' => $data['need_driver_pickup'],
            ':pickup_time' => $data['pickup_time']
        ]);

        return $result ? $this->db->lastInsertId() : false;
    }

    // Approve intrastate request (move to next level)
    public function approveIntrastateRequest($id, $current_level, $approver_email) {
        // First get the request
        $request = $this->getIntrastateRequestById($id);
        if (!$request) {
            return false;
        }
        
        $next_level = 'none';
        $status = 'approved';
        $current_time = date('Y-m-d H:i:s');
        
        // Update approval timestamp based on current level
        if ($current_level == 'reviewer') {
            $sql = "UPDATE intrastate_request SET reviewer_approved_at = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$current_time, $id]);
            
            // Check if co-reviewer exists
            if ($request->co_reviewer_email && !empty($request->co_reviewer_email)) {
                $next_level = 'co_reviewer';
                $status = 'pending';
            } else {
                $next_level = 'manager';
                $status = 'pending';
            }
        } elseif ($current_level == 'co_reviewer') {
            $sql = "UPDATE intrastate_request SET co_reviewer_approved_at = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$current_time, $id]);
            $next_level = 'manager';
            $status = 'pending';
        } elseif ($current_level == 'manager') {
            $sql = "UPDATE intrastate_request SET manager_approved_at = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$current_time, $id]);
            $next_level = 'security_manager';
            $status = 'pending';
        } elseif ($current_level == 'security_manager') {
            $sql = "UPDATE intrastate_request SET security_manager_approved_at = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$current_time, $id]);
            $next_level = 'none';
            $status = 'security_approved';
        }
        
        $sql = "UPDATE intrastate_request SET status = ?, current_approval_level = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $next_level, $id]);
    }

    // Reject intrastate request
    public function rejectIntrastateRequest($id, $reason, $rejected_by) {
        $sql = "UPDATE intrastate_request 
                SET status = 'rejected', 
                    rejection_reason = ?, 
                    rejected_by = ?, 
                    rejected_at = NOW() 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$reason, $rejected_by, $id]);
    }

    // Assign driver to request (by security manager - now final approver)
    public function assignDriverToRequest($request_id, $driver_id) {
        $sql = "UPDATE intrastate_request SET assigned_driver_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$driver_id, $request_id]);
    }

    // Complete the trip request
    public function completeIntrastateRequest($id) {
        $sql = "UPDATE intrastate_request SET status = 'completed' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Cancel intrastate request
    public function cancelIntrastateRequest($id) {
        $sql = "UPDATE intrastate_request SET status = 'cancelled' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Delete intrastate request (only draft or cancelled)
    public function deleteIntrastateRequest($id) {
        $sql = "DELETE FROM intrastate_request WHERE id = ? AND status IN ('draft', 'cancelled')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Get EA state configuration by state ID (without operations manager)
    public function getEaStateConfigByStateId($state_id) {
        $sql = "SELECT es.*, 
                d.email as driver_email, d.phone as driver_phone
                FROM ea_state es
                LEFT JOIN driver d ON es.driver_id = d.id
                WHERE es.state_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$state_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Get all EA states for dropdown
    public function getAllEaStatesForDropdown() {
        $sql = "SELECT es.state_id, s.name as state_name, s.code as state_code, c.name as country_name
                FROM ea_state es
                JOIN state s ON es.state_id = s.id
                JOIN country c ON s.country_id = c.id
                ORDER BY c.name, s.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get all admin/super_admin emails for supervisor and overtime manager dropdown
    public function getAdminSupervisorEmails() {
        $sql = "SELECT email, role, CONCAT(SUBSTRING_INDEX(email, '@', 1)) as name 
                FROM staff_login 
                WHERE role IN ('admin', 'super_admin') 
                ORDER BY email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get all funder codes for dropdown
    public function getAllFunderCodesForDropdown() {
        $sql = "SELECT id, name FROM funder_codes ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get all drivers for dropdown (for security manager assignment - now final approver)
    public function getAllAvailableDrivers() {
        $sql = "SELECT id, email, phone, SUBSTRING_INDEX(email, '@', 1) as driver_name 
                FROM driver 
                ORDER BY email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Calculate total nights between two dates
    public function calculateTotalNights($start_date, $end_date) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        return $interval->days;
    }

    // Get request counts by status for dashboard
    public function getIntrastateRequestCounts($staff_email = null) {
        if ($staff_email) {
            $sql = "SELECT status, COUNT(*) as count FROM intrastate_request WHERE staff_email = ? GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$staff_email]);
        } else {
            $sql = "SELECT status, COUNT(*) as count FROM intrastate_request GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        $counts = [];
        foreach ($results as $row) {
            $counts[$row->status] = $row->count;
        }
        return $counts;
    }

    // Get pending approval counts for a specific approver
    public function getPendingApprovalCounts($approver_email) {
        $counts = [];
        
        // Check as reviewer
        $sql = "SELECT COUNT(*) as count FROM intrastate_request WHERE status = 'pending' AND current_approval_level = 'reviewer' AND reviewer_email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$approver_email]);
        $counts['reviewer'] = $stmt->fetch(PDO::FETCH_OBJ)->count;
        
        // Check as co-reviewer
        $sql = "SELECT COUNT(*) as count FROM intrastate_request WHERE status = 'pending' AND current_approval_level = 'co_reviewer' AND co_reviewer_email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$approver_email]);
        $counts['co_reviewer'] = $stmt->fetch(PDO::FETCH_OBJ)->count;
        
        // Check as manager
        $sql = "SELECT COUNT(*) as count FROM intrastate_request WHERE status = 'pending' AND current_approval_level = 'manager' AND manager_email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$approver_email]);
        $counts['manager'] = $stmt->fetch(PDO::FETCH_OBJ)->count;
        
        // Check as security manager
        $sql = "SELECT COUNT(*) as count FROM intrastate_request WHERE status = 'pending' AND current_approval_level = 'security_manager' AND security_manager_email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$approver_email]);
        $counts['security_manager'] = $stmt->fetch(PDO::FETCH_OBJ)->count;
        
        return $counts;
    }

    // Update intrastate request (for editing draft)
    public function updateIntrastateRequest($id, $data) {
        $sql = "UPDATE intrastate_request SET 
                staff_phone = :staff_phone,
                supervisor_email = :supervisor_email,
                vehicle_location_state_id = :vehicle_location_state_id,
                reviewer_email = :reviewer_email,
                co_reviewer_email = :co_reviewer_email,
                manager_email = :manager_email,
                security_manager_email = :security_manager_email,
                trip_date = :trip_date,
                return_date = :return_date,
                total_nights = :total_nights,
                purpose = :purpose,
                pickup_location = :pickup_location,
                trip_destination = :trip_destination,
                trip_destination_time = :trip_destination_time,
                route_information = :route_information,
                funder_code_id = :funder_code_id,
                driver_overtime = :driver_overtime,
                trip_activity = :trip_activity,
                reason_for_overtime = :reason_for_overtime,
                overtime_manager_email = :overtime_manager_email,
                need_driver_pickup = :need_driver_pickup,
                pickup_time = :pickup_time
                WHERE id = :id AND status = 'draft'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    // Submit draft for approval
    public function submitIntrastateRequest($id) {
        $sql = "UPDATE intrastate_request SET status = 'pending', current_approval_level = 'reviewer' WHERE id = ? AND status = 'draft'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Save as draft
    public function saveIntrastateRequestDraft($data) {
        $sql = "INSERT INTO intrastate_request (
            staff_email, staff_phone, supervisor_email, vehicle_location_state_id,
            reviewer_email, co_reviewer_email, manager_email, security_manager_email,
            trip_date, return_date, total_nights, purpose, pickup_location,
            trip_destination, trip_destination_time, route_information, funder_code_id,
            driver_overtime, trip_activity, reason_for_overtime, overtime_manager_email,
            need_driver_pickup, pickup_time, status, current_approval_level
        ) VALUES (
            :staff_email, :staff_phone, :supervisor_email, :vehicle_location_state_id,
            :reviewer_email, :co_reviewer_email, :manager_email, :security_manager_email,
            :trip_date, :return_date, :total_nights, :purpose, :pickup_location,
            :trip_destination, :trip_destination_time, :route_information, :funder_code_id,
            :driver_overtime, :trip_activity, :reason_for_overtime, :overtime_manager_email,
            :need_driver_pickup, :pickup_time, 'draft', 'reviewer'
        )";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':staff_email' => $data['staff_email'],
            ':staff_phone' => $data['staff_phone'],
            ':supervisor_email' => $data['supervisor_email'],
            ':vehicle_location_state_id' => $data['vehicle_location_state_id'],
            ':reviewer_email' => $data['reviewer_email'],
            ':co_reviewer_email' => $data['co_reviewer_email'],
            ':manager_email' => $data['manager_email'],
            ':security_manager_email' => $data['security_manager_email'],
            ':trip_date' => $data['trip_date'],
            ':return_date' => $data['return_date'],
            ':total_nights' => $data['total_nights'],
            ':purpose' => $data['purpose'],
            ':pickup_location' => $data['pickup_location'],
            ':trip_destination' => $data['trip_destination'],
            ':trip_destination_time' => $data['trip_destination_time'],
            ':route_information' => $data['route_information'],
            ':funder_code_id' => $data['funder_code_id'],
            ':driver_overtime' => $data['driver_overtime'],
            ':trip_activity' => $data['trip_activity'],
            ':reason_for_overtime' => $data['reason_for_overtime'],
            ':overtime_manager_email' => $data['overtime_manager_email'],
            ':need_driver_pickup' => $data['need_driver_pickup'],
            ':pickup_time' => $data['pickup_time']
        ]);
    }

    ///////////////////////////////////////////////////////////////////////////
    ////////////Interstate (One State to Another) Requests Functions
    ///////////////////////////////////////////////////////////////////////////

    /**
     * Get all interstate requests
     * @return array Array of interstate request objects
     */
    public function getAllInterstateRequests()
    {
        $sql = "SELECT ir.*,
                CONCAT(SUBSTRING_INDEX(ir.staff_email, '@', 1)) as staff_name,
                CONCAT(SUBSTRING_INDEX(ir.supervisor_email, '@', 1)) as supervisor_name,
                vs.name as vehicle_location_state_name, vs.code as vehicle_location_code,
                ars.name as arrival_state_name, ars.code as arrival_state_code,
                fc.name as funder_code_name,
                CONCAT(SUBSTRING_INDEX(ir.reviewer_email, '@', 1)) as reviewer_name,
                CONCAT(SUBSTRING_INDEX(ir.co_reviewer_email, '@', 1)) as co_reviewer_name,
                CONCAT(SUBSTRING_INDEX(ir.manager_email, '@', 1)) as manager_name,
                CONCAT(SUBSTRING_INDEX(ir.security_manager_email, '@', 1)) as security_manager_name,
                CONCAT(SUBSTRING_INDEX(ir.overtime_manager_email, '@', 1)) as overtime_manager_name,
                d.email as driver_email, d.phone as driver_phone,
                CONCAT(SUBSTRING_INDEX(d.email, '@', 1)) as driver_name,
                rd.email as return_driver_email, rd.phone as return_driver_phone,
                CONCAT(SUBSTRING_INDEX(rd.email, '@', 1)) as return_driver_name,
                a1.name as requester_departure_airline,
                a2.name as requester_return_airline,
                a3.name as operations_departure_airline,
                a4.name as operations_return_airline,
                h.name as hotel_name_from_vendor
                FROM interstate_request ir
                JOIN state vs ON ir.vehicle_location_state_id = vs.id
                JOIN state ars ON ir.arrival_location_state_id = ars.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                LEFT JOIN driver d ON ir.assigned_driver_id = d.id
                LEFT JOIN driver rd ON ir.return_assigned_driver_id = rd.id
                LEFT JOIN airlines a1 ON ir.requester_departure_flight_airline_id = a1.id
                LEFT JOIN airlines a2 ON ir.requester_return_flight_airline_id = a2.id
                LEFT JOIN airlines a3 ON ir.operations_departure_flight_airline_id = a3.id
                LEFT JOIN airlines a4 ON ir.operations_return_flight_airline_id = a4.id
                LEFT JOIN hotel h ON ir.hotel_id = h.id
                ORDER BY ir.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get interstate request by ID
     * @param int $id Request ID
     * @return object|false Request object or false if not found
     */
    public function getInterstateRequestById($id)
    {
        $sql = "SELECT ir.*,
                CONCAT(SUBSTRING_INDEX(ir.staff_email, '@', 1)) as staff_name,
                CONCAT(SUBSTRING_INDEX(ir.supervisor_email, '@', 1)) as supervisor_name,
                vs.name as vehicle_location_state_name, vs.code as vehicle_location_code,
                ars.name as arrival_state_name, ars.code as arrival_state_code,
                fc.name as funder_code_name,
                CONCAT(SUBSTRING_INDEX(ir.reviewer_email, '@', 1)) as reviewer_name,
                CONCAT(SUBSTRING_INDEX(ir.co_reviewer_email, '@', 1)) as co_reviewer_name,
                CONCAT(SUBSTRING_INDEX(ir.manager_email, '@', 1)) as manager_name,
                CONCAT(SUBSTRING_INDEX(ir.security_manager_email, '@', 1)) as security_manager_name,
                CONCAT(SUBSTRING_INDEX(ir.overtime_manager_email, '@', 1)) as overtime_manager_name,
                d.email as driver_email, d.phone as driver_phone,
                CONCAT(SUBSTRING_INDEX(d.email, '@', 1)) as driver_name,
                rd.email as return_driver_email, rd.phone as return_driver_phone,
                CONCAT(SUBSTRING_INDEX(rd.email, '@', 1)) as return_driver_name,
                a1.name as requester_departure_airline,
                a2.name as requester_return_airline,
                a3.name as operations_departure_airline,
                a4.name as operations_return_airline,
                h.name as hotel_name_from_vendor, h.state_id as hotel_state_id
                FROM interstate_request ir
                JOIN state vs ON ir.vehicle_location_state_id = vs.id
                JOIN state ars ON ir.arrival_location_state_id = ars.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                LEFT JOIN driver d ON ir.assigned_driver_id = d.id
                LEFT JOIN driver rd ON ir.return_assigned_driver_id = rd.id
                LEFT JOIN airlines a1 ON ir.requester_departure_flight_airline_id = a1.id
                LEFT JOIN airlines a2 ON ir.requester_return_flight_airline_id = a2.id
                LEFT JOIN airlines a3 ON ir.operations_departure_flight_airline_id = a3.id
                LEFT JOIN airlines a4 ON ir.operations_return_flight_airline_id = a4.id
                LEFT JOIN hotel h ON ir.hotel_id = h.id
                WHERE ir.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get interstate requests by staff email
     * @param string $staff_email Staff email address
     * @return array Array of request objects
     */
    public function getInterstateRequestsByStaff($staff_email)
    {
        $sql = "SELECT ir.*,
                vs.name as vehicle_location_state_name,
                ars.name as arrival_state_name,
                fc.name as funder_code_name,
                CASE 
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'reviewer' THEN 'Waiting for Reviewer'
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'co_reviewer' THEN 'Waiting for Co-Reviewer'
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'manager' THEN 'Waiting for Manager'
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'security_manager' THEN 'Waiting for Security Manager'
                    WHEN ir.status = 'reviewer_approved' THEN 'Approved by Reviewer'
                    WHEN ir.status = 'co_reviewer_approved' THEN 'Approved by Co-Reviewer'
                    WHEN ir.status = 'manager_approved' THEN 'Approved by Manager'
                    WHEN ir.status = 'security_approved' THEN 'Approved - Driver Assigned'
                    WHEN ir.status = 'rejected' THEN 'Rejected'
                    WHEN ir.status = 'completed' THEN 'Completed'
                    WHEN ir.status = 'cancelled' THEN 'Cancelled'
                    WHEN ir.status = 'draft' THEN 'Draft'
                    ELSE REPLACE(ir.status, '_', ' ')
                END as approval_status_text
                FROM interstate_request ir
                JOIN state vs ON ir.vehicle_location_state_id = vs.id
                JOIN state ars ON ir.arrival_location_state_id = ars.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                WHERE ir.staff_email = ?
                ORDER BY ir.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$staff_email]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get interstate requests pending for specific approver
     * @param string $approver_email Approver email address
     * @param string $level Approval level (reviewer, co_reviewer, manager, security_manager)
     * @return array Array of request objects
     */
    public function getInterstateRequestsByApprover($approver_email, $level)
    {
        $sql = "SELECT ir.*,
                vs.name as vehicle_location_state_name, vs.code as vehicle_location_code,
                ars.name as arrival_state_name,
                fc.name as funder_code_name,
                CONCAT(SUBSTRING_INDEX(ir.staff_email, '@', 1)) as staff_name,
                ir.staff_phone,
                ir.purpose,
                ir.trip_destination,
                ir.destination_city,
                ir.trip_date,
                ir.return_date,
                ir.mode_of_travel,
                ir.require_hotel
                FROM interstate_request ir
                JOIN state vs ON ir.vehicle_location_state_id = vs.id
                JOIN state ars ON ir.arrival_location_state_id = ars.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                WHERE ir.status = 'pending' AND ir.current_approval_level = ?";
        
        if ($level == 'reviewer') {
            $sql .= " AND ir.reviewer_email = ?";
        } elseif ($level == 'co_reviewer') {
            $sql .= " AND ir.co_reviewer_email = ?";
        } elseif ($level == 'manager') {
            $sql .= " AND ir.manager_email = ?";
        } elseif ($level == 'security_manager') {
            $sql .= " AND ir.security_manager_email = ?";
        }
        
        $sql .= " ORDER BY ir.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$level, $approver_email]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get interstate requests by supervisor
     * @param string $supervisor_email Supervisor email address
     * @return array Array of request objects
     */
    public function getInterstateRequestsBySupervisor($supervisor_email)
    {
        $sql = "SELECT ir.*,
                CONCAT(SUBSTRING_INDEX(ir.staff_email, '@', 1)) as staff_name,
                vs.name as vehicle_location_state_name, vs.code as vehicle_location_code,
                ars.name as arrival_state_name,
                fc.name as funder_code_name,
                ir.staff_phone,
                ir.purpose,
                ir.trip_destination,
                ir.destination_city,
                ir.trip_date,
                ir.return_date,
                ir.mode_of_travel,
                CASE 
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'reviewer' THEN 'Pending Your Approval'
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'co_reviewer' THEN 'With Co-Reviewer'
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'manager' THEN 'With Manager'
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'security_manager' THEN 'With Security Manager'
                    WHEN ir.status = 'reviewer_approved' THEN 'Approved by Reviewer'
                    WHEN ir.status = 'co_reviewer_approved' THEN 'Approved by Co-Reviewer'
                    WHEN ir.status = 'manager_approved' THEN 'Approved by Manager'
                    WHEN ir.status = 'security_approved' THEN 'Approved - Driver Assigned'
                    WHEN ir.status = 'rejected' THEN 'Rejected'
                    WHEN ir.status = 'completed' THEN 'Completed'
                    WHEN ir.status = 'cancelled' THEN 'Cancelled'
                    ELSE REPLACE(ir.status, '_', ' ')
                END as approval_status_text
                FROM interstate_request ir
                JOIN state vs ON ir.vehicle_location_state_id = vs.id
                JOIN state ars ON ir.arrival_location_state_id = ars.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                WHERE ir.supervisor_email = ?
                ORDER BY ir.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$supervisor_email]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Create new interstate request
     * @param array $data Request data array
     * @return int|false Last insert ID on success, false on failure
     */
    public function createInterstateRequest($data)
    {
        $sql = "INSERT INTO interstate_request (
            staff_email, staff_phone, supervisor_email, vehicle_location_state_id,
            reviewer_email, co_reviewer_email, manager_email, security_manager_email,
            trip_date, return_date, total_nights, purpose, arrival_location_state_id,
            destination_city, pickup_location, trip_destination, trip_destination_time,
            route_information, mode_of_travel, require_airport_pickup, airport_pickup_dropoff_destination,
            requester_departure_flight_airline_id, requester_return_flight_airline_id,
            require_hotel, hotel_id, hotel_other_name, hotel_location, hotel_location_state_id,
            funder_code_id, driver_overtime, trip_activity, reason_for_overtime,
            overtime_manager_email, need_driver_pickup, pickup_time, status, current_approval_level
        ) VALUES (
            :staff_email, :staff_phone, :supervisor_email, :vehicle_location_state_id,
            :reviewer_email, :co_reviewer_email, :manager_email, :security_manager_email,
            :trip_date, :return_date, :total_nights, :purpose, :arrival_location_state_id,
            :destination_city, :pickup_location, :trip_destination, :trip_destination_time,
            :route_information, :mode_of_travel, :require_airport_pickup, :airport_pickup_dropoff_destination,
            :requester_departure_flight_airline_id, :requester_return_flight_airline_id,
            :require_hotel, :hotel_id, :hotel_other_name, :hotel_location, :hotel_location_state_id,
            :funder_code_id, :driver_overtime, :trip_activity, :reason_for_overtime,
            :overtime_manager_email, :need_driver_pickup, :pickup_time, :status, :current_approval_level
        )";
        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            ':staff_email' => $data['staff_email'],
            ':staff_phone' => $data['staff_phone'],
            ':supervisor_email' => $data['supervisor_email'],
            ':vehicle_location_state_id' => $data['vehicle_location_state_id'],
            ':reviewer_email' => $data['reviewer_email'],
            ':co_reviewer_email' => $data['co_reviewer_email'],
            ':manager_email' => $data['manager_email'],
            ':security_manager_email' => $data['security_manager_email'],
            ':trip_date' => $data['trip_date'],
            ':return_date' => $data['return_date'],
            ':total_nights' => $data['total_nights'],
            ':purpose' => $data['purpose'],
            ':arrival_location_state_id' => $data['arrival_location_state_id'],
            ':destination_city' => $data['destination_city'],
            ':pickup_location' => $data['pickup_location'],
            ':trip_destination' => $data['trip_destination'],
            ':trip_destination_time' => $data['trip_destination_time'],
            ':route_information' => $data['route_information'],
            ':mode_of_travel' => $data['mode_of_travel'],
            ':require_airport_pickup' => $data['require_airport_pickup'],
            ':airport_pickup_dropoff_destination' => $data['airport_pickup_dropoff_destination'],
            ':requester_departure_flight_airline_id' => $data['requester_departure_flight_airline_id'],
            ':requester_return_flight_airline_id' => $data['requester_return_flight_airline_id'],
            ':require_hotel' => $data['require_hotel'],
            ':hotel_id' => $data['hotel_id'],
            ':hotel_other_name' => $data['hotel_other_name'],
            ':hotel_location' => $data['hotel_location'],
            ':hotel_location_state_id' => $data['hotel_location_state_id'],
            ':funder_code_id' => $data['funder_code_id'],
            ':driver_overtime' => $data['driver_overtime'],
            ':trip_activity' => $data['trip_activity'],
            ':reason_for_overtime' => $data['reason_for_overtime'],
            ':overtime_manager_email' => $data['overtime_manager_email'],
            ':need_driver_pickup' => $data['need_driver_pickup'],
            ':pickup_time' => $data['pickup_time'],
            ':status' => $data['status'],
            ':current_approval_level' => $data['current_approval_level']
        ]);

        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Save interstate request as draft
     * @param array $data Request data array
     * @return bool True on success, false on failure
     */
    public function saveInterstateRequestDraft($data)
    {
        $sql = "INSERT INTO interstate_request (
            staff_email, staff_phone, supervisor_email, vehicle_location_state_id,
            reviewer_email, co_reviewer_email, manager_email, security_manager_email,
            trip_date, return_date, total_nights, purpose, arrival_location_state_id,
            destination_city, pickup_location, trip_destination, trip_destination_time,
            route_information, mode_of_travel, require_airport_pickup, airport_pickup_dropoff_destination,
            requester_departure_flight_airline_id, requester_return_flight_airline_id,
            require_hotel, hotel_id, hotel_other_name, hotel_location, hotel_location_state_id,
            funder_code_id, driver_overtime, trip_activity, reason_for_overtime,
            overtime_manager_email, need_driver_pickup, pickup_time, status, current_approval_level
        ) VALUES (
            :staff_email, :staff_phone, :supervisor_email, :vehicle_location_state_id,
            :reviewer_email, :co_reviewer_email, :manager_email, :security_manager_email,
            :trip_date, :return_date, :total_nights, :purpose, :arrival_location_state_id,
            :destination_city, :pickup_location, :trip_destination, :trip_destination_time,
            :route_information, :mode_of_travel, :require_airport_pickup, :airport_pickup_dropoff_destination,
            :requester_departure_flight_airline_id, :requester_return_flight_airline_id,
            :require_hotel, :hotel_id, :hotel_other_name, :hotel_location, :hotel_location_state_id,
            :funder_code_id, :driver_overtime, :trip_activity, :reason_for_overtime,
            :overtime_manager_email, :need_driver_pickup, :pickup_time, 'draft', 'reviewer'
        )";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':staff_email' => $data['staff_email'],
            ':staff_phone' => $data['staff_phone'],
            ':supervisor_email' => $data['supervisor_email'],
            ':vehicle_location_state_id' => $data['vehicle_location_state_id'],
            ':reviewer_email' => $data['reviewer_email'],
            ':co_reviewer_email' => $data['co_reviewer_email'],
            ':manager_email' => $data['manager_email'],
            ':security_manager_email' => $data['security_manager_email'],
            ':trip_date' => $data['trip_date'],
            ':return_date' => $data['return_date'],
            ':total_nights' => $data['total_nights'],
            ':purpose' => $data['purpose'],
            ':arrival_location_state_id' => $data['arrival_location_state_id'],
            ':destination_city' => $data['destination_city'],
            ':pickup_location' => $data['pickup_location'],
            ':trip_destination' => $data['trip_destination'],
            ':trip_destination_time' => $data['trip_destination_time'],
            ':route_information' => $data['route_information'],
            ':mode_of_travel' => $data['mode_of_travel'],
            ':require_airport_pickup' => $data['require_airport_pickup'],
            ':airport_pickup_dropoff_destination' => $data['airport_pickup_dropoff_destination'],
            ':requester_departure_flight_airline_id' => $data['requester_departure_flight_airline_id'],
            ':requester_return_flight_airline_id' => $data['requester_return_flight_airline_id'],
            ':require_hotel' => $data['require_hotel'],
            ':hotel_id' => $data['hotel_id'],
            ':hotel_other_name' => $data['hotel_other_name'],
            ':hotel_location' => $data['hotel_location'],
            ':hotel_location_state_id' => $data['hotel_location_state_id'],
            ':funder_code_id' => $data['funder_code_id'],
            ':driver_overtime' => $data['driver_overtime'],
            ':trip_activity' => $data['trip_activity'],
            ':reason_for_overtime' => $data['reason_for_overtime'],
            ':overtime_manager_email' => $data['overtime_manager_email'],
            ':need_driver_pickup' => $data['need_driver_pickup'],
            ':pickup_time' => $data['pickup_time']
        ]);
    }

    /**
     * Update interstate request (for editing draft)
     * @param int $id Request ID
     * @param array $data Request data array
     * @return bool True on success, false on failure
     */
    public function updateInterstateRequest($id, $data)
    {
        $sql = "UPDATE interstate_request SET 
                staff_phone = :staff_phone,
                supervisor_email = :supervisor_email,
                vehicle_location_state_id = :vehicle_location_state_id,
                trip_date = :trip_date,
                return_date = :return_date,
                total_nights = :total_nights,
                purpose = :purpose,
                arrival_location_state_id = :arrival_location_state_id,
                destination_city = :destination_city,
                pickup_location = :pickup_location,
                trip_destination = :trip_destination,
                trip_destination_time = :trip_destination_time,
                route_information = :route_information,
                mode_of_travel = :mode_of_travel,
                require_airport_pickup = :require_airport_pickup,
                airport_pickup_dropoff_destination = :airport_pickup_dropoff_destination,
                requester_departure_flight_airline_id = :requester_departure_flight_airline_id,
                requester_return_flight_airline_id = :requester_return_flight_airline_id,
                require_hotel = :require_hotel,
                hotel_id = :hotel_id,
                hotel_other_name = :hotel_other_name,
                hotel_location = :hotel_location,
                hotel_location_state_id = :hotel_location_state_id,
                funder_code_id = :funder_code_id,
                driver_overtime = :driver_overtime,
                trip_activity = :trip_activity,
                reason_for_overtime = :reason_for_overtime,
                overtime_manager_email = :overtime_manager_email,
                need_driver_pickup = :need_driver_pickup,
                pickup_time = :pickup_time
                WHERE id = :id AND status = 'draft'";
        $stmt = $this->db->prepare($sql);
        
        $data[':id'] = $id;
        return $stmt->execute($data);
    }

    /**
     * Submit draft for approval
     * @param int $id Request ID
     * @return bool True on success, false on failure
     */
    public function submitInterstateRequest($id)
    {
    $sql = "UPDATE interstate_request SET status = 'pending', current_approval_level = 'reviewer' WHERE id = ? AND status = 'draft'";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$id]);
    }

    /**
     * Approve interstate request (move to next level)
     * @param int $id Request ID
     * @param string $current_level Current approval level
     * @param string $approver_email Approver email address
     * @return bool True on success, false on failure
     */
    public function approveInterstateRequest($id, $current_level, $approver_email)
    {
        // First get the request
        $request = $this->getInterstateRequestById($id);
        if (!$request) {
            return false;
        }
        
        $next_level = 'none';
        $status = 'approved';
        $current_time = date('Y-m-d H:i:s');
        
        // Update approval timestamp based on current level
        if ($current_level == 'reviewer') {
            $sql = "UPDATE interstate_request SET reviewer_approved_at = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$current_time, $id]);
            
            // Check if co-reviewer exists
            if ($request->co_reviewer_email && !empty($request->co_reviewer_email)) {
                $next_level = 'co_reviewer';
                $status = 'pending';
            } else {
                $next_level = 'manager';
                $status = 'pending';
            }
        } elseif ($current_level == 'co_reviewer') {
            $sql = "UPDATE interstate_request SET co_reviewer_approved_at = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$current_time, $id]);
            $next_level = 'manager';
            $status = 'pending';
        } elseif ($current_level == 'manager') {
            $sql = "UPDATE interstate_request SET manager_approved_at = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$current_time, $id]);
            $next_level = 'security_manager';
            $status = 'pending';
        } elseif ($current_level == 'security_manager') {
            $sql = "UPDATE interstate_request SET security_manager_approved_at = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$current_time, $id]);
            $next_level = 'none';
            $status = 'security_approved';
        }
        
        $sql = "UPDATE interstate_request SET status = ?, current_approval_level = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $next_level, $id]);
    }

    /**
     * Reject interstate request
     * @param int $id Request ID
     * @param string $reason Rejection reason
     * @param string $rejected_by Email of person rejecting
     * @return bool True on success, false on failure
     */
    public function rejectInterstateRequest($id, $reason, $rejected_by)
    {
        $sql = "UPDATE interstate_request 
                SET status = 'rejected', 
                    current_approval_level = 'none',
                    rejection_reason = ?, 
                    rejected_by = ?, 
                    rejected_at = NOW() 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$reason, $rejected_by, $id]);
    }

    /**
     * Update flight details by operations
     * @param int $id Request ID
     * @param int|null $departure_airline_id Departure airline ID
     * @param int|null $return_airline_id Return airline ID
     * @param string|null $flight_arrival_time Flight arrival time
     * @return bool True on success, false on failure
     */
    public function updateFlightDetails($id, $departure_airline_id, $return_airline_id, $flight_arrival_time)
    {
        $sql = "UPDATE interstate_request 
                SET operations_departure_flight_airline_id = :departure_airline,
                    operations_return_flight_airline_id = :return_airline,
                    flight_arrival_time = :flight_arrival_time
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':departure_airline' => $departure_airline_id,
            ':return_airline' => $return_airline_id,
            ':flight_arrival_time' => $flight_arrival_time
        ]);
    }

    /**
     * Assign driver to interstate request (by security manager)
     * @param int $id Request ID
     * @param int $assigned_driver_id Driver ID
     * @param string $different_return_driver Whether different driver for return
     * @param int|null $return_assigned_driver_id Return driver ID
     * @return bool True on success, false on failure
     */
    public function assignDriverToInterstateRequest($id, $assigned_driver_id, $different_return_driver = 'no', $return_assigned_driver_id = null)
    {
        // Get driver details to populate approved driver fields
        $driver = null;
        if ($assigned_driver_id) {
            $driver = $this->getDriverById($assigned_driver_id);
        }
        
        $sql = "UPDATE interstate_request 
                SET assigned_driver_id = :assigned_driver_id,
                    different_return_driver = :different_return_driver,
                    return_assigned_driver_id = :return_assigned_driver_id,
                    approved_driver_email = :approved_driver_email,
                    approved_driver_phone = :approved_driver_phone,
                    status = 'security_approved',
                    security_manager_approved_at = NOW(),
                    current_approval_level = 'none'
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':assigned_driver_id' => $assigned_driver_id,
            ':different_return_driver' => $different_return_driver,
            ':return_assigned_driver_id' => $return_assigned_driver_id,
            ':approved_driver_email' => $driver ? $driver->email : null,
            ':approved_driver_phone' => $driver ? $driver->phone : null
        ]);
    }

    /**
     * Operations team assignment: confirms/overrides flights, hotel, and assigns driver(s)
     */
    public function operationsAssignInterstate($id, $data)
    {
        $driver = $this->getDriverById($data['assigned_driver_id']);
        $returnDriver = null;
        if (!empty($data['return_assigned_driver_id'])) {
            $returnDriver = $this->getDriverById($data['return_assigned_driver_id']);
        }

        $sql = "UPDATE interstate_request SET
                    operations_departure_flight_airline_id = :ops_dep_airline,
                    operations_return_flight_airline_id    = :ops_ret_airline,
                    hotel_id                               = :hotel_id,
                    hotel_other_name                       = :hotel_other_name,
                    hotel_location                         = :hotel_location,
                    assigned_driver_id                     = :assigned_driver_id,
                    approved_driver_email                  = :approved_driver_email,
                    approved_driver_phone                  = :approved_driver_phone,
                    different_return_driver                = :different_return_driver,
                    return_assigned_driver_id              = :return_assigned_driver_id,
                    current_approval_level                 = 'none'
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':ops_dep_airline'        => $data['operations_departure_flight_airline_id'] ?: null,
            ':ops_ret_airline'        => $data['operations_return_flight_airline_id'] ?: null,
            ':hotel_id'               => $data['hotel_id'] ?: null,
            ':hotel_other_name'       => $data['hotel_other_name'] ?: null,
            ':hotel_location'         => $data['hotel_location'] ?: null,
            ':assigned_driver_id'     => $data['assigned_driver_id'],
            ':approved_driver_email'  => $driver ? $driver->email : null,
            ':approved_driver_phone'  => $driver ? $driver->phone : null,
            ':different_return_driver'    => $data['different_return_driver'] ?? 'no',
            ':return_assigned_driver_id'  => !empty($data['return_assigned_driver_id']) ? $data['return_assigned_driver_id'] : null,
            ':id'                     => $id
        ]);
    }

    /**
     * Complete interstate request
     * @param int $id Request ID
     * @return bool True on success, false on failure
     */
    public function completeInterstateRequest($id)
    {
        $sql = "UPDATE interstate_request SET status = 'completed' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Cancel interstate request
     * @param int $id Request ID
     * @return bool True on success, false on failure
     */
    public function cancelInterstateRequest($id)
    {
        $sql = "UPDATE interstate_request SET status = 'cancelled' WHERE id = ? AND status IN ('draft', 'pending', 'reviewer_approved', 'co_reviewer_approved', 'manager_approved')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Delete interstate request (only draft or cancelled)
     * @param int $id Request ID
     * @return bool True on success, false on failure
     */
    public function deleteInterstateRequest($id)
    {
        $sql = "DELETE FROM interstate_request WHERE id = ? AND status IN ('draft', 'cancelled')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Get interstate requests awaiting driver assignment (security_approved with no driver)
     * @return array Array of request objects
     */
    public function getInterstateRequestsAwaitingDriver()
    {
        $sql = "SELECT ir.*,
                CONCAT(SUBSTRING_INDEX(ir.staff_email, '@', 1)) as staff_name,
                vs.name as vehicle_location_state_name, vs.code as vehicle_location_code,
                ars.name as arrival_state_name,
                fc.name as funder_code_name,
                CONCAT(SUBSTRING_INDEX(ir.supervisor_email, '@', 1)) as supervisor_name,
                a1.name as requester_departure_airline,
                a2.name as requester_return_airline,
                h.name as hotel_name_from_vendor
                FROM interstate_request ir
                JOIN state vs ON ir.vehicle_location_state_id = vs.id
                JOIN state ars ON ir.arrival_location_state_id = ars.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                LEFT JOIN airlines a1 ON ir.requester_departure_flight_airline_id = a1.id
                LEFT JOIN airlines a2 ON ir.requester_return_flight_airline_id = a2.id
                LEFT JOIN hotel h ON ir.hotel_id = h.id
                WHERE ir.status = 'security_approved'
                AND (ir.assigned_driver_id IS NULL OR ir.assigned_driver_id = 0)
                ORDER BY ir.trip_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get interstate requests in progress (driver assigned but not completed)
     * @return array Array of request objects
     */
    public function getInterstateRequestsInProgress()
    {
        $sql = "SELECT ir.*,
                CONCAT(SUBSTRING_INDEX(ir.staff_email, '@', 1)) as staff_name,
                vs.name as vehicle_location_state_name, vs.code as vehicle_location_code,
                ars.name as arrival_state_name,
                fc.name as funder_code_name,
                d.email as driver_email, d.phone as driver_phone,
                CONCAT(SUBSTRING_INDEX(d.email, '@', 1)) as driver_name,
                rd.email as return_driver_email, rd.phone as return_driver_phone,
                CONCAT(SUBSTRING_INDEX(rd.email, '@', 1)) as return_driver_name
                FROM interstate_request ir
                JOIN state vs ON ir.vehicle_location_state_id = vs.id
                JOIN state ars ON ir.arrival_location_state_id = ars.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                LEFT JOIN driver d ON ir.assigned_driver_id = d.id
                LEFT JOIN driver rd ON ir.return_assigned_driver_id = rd.id
                WHERE ir.status = 'security_approved' 
                AND ir.assigned_driver_id IS NOT NULL 
                AND ir.assigned_driver_id != 0
                ORDER BY ir.trip_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get request counts by status for dashboard
     * @param string|null $staff_email Staff email (optional, for user-specific counts)
     * @return array Array of counts by status
     */
    public function getInterstateRequestCounts($staff_email = null)
    {
        if ($staff_email) {
            $sql = "SELECT status, COUNT(*) as count FROM interstate_request WHERE staff_email = ? GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$staff_email]);
        } else {
            $sql = "SELECT status, COUNT(*) as count FROM interstate_request GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        $counts = [];
        foreach ($results as $row) {
            $counts[$row->status] = $row->count;
        }
        return $counts;
    }

    /**
     * Get pending approval counts for a specific approver
     * @param string $approver_email Approver email address
     * @return array Array of counts by approval level
     */
    public function getInterstatePendingApprovalCounts($approver_email)
    {
        $counts = [];
        
        // Check as reviewer
        $sql = "SELECT COUNT(*) as count FROM interstate_request WHERE status = 'pending' AND current_approval_level = 'reviewer' AND reviewer_email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$approver_email]);
        $counts['reviewer'] = $stmt->fetch(PDO::FETCH_OBJ)->count;
        
        // Check as co-reviewer
        $sql = "SELECT COUNT(*) as count FROM interstate_request WHERE status = 'pending' AND current_approval_level = 'co_reviewer' AND co_reviewer_email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$approver_email]);
        $counts['co_reviewer'] = $stmt->fetch(PDO::FETCH_OBJ)->count;
        
        // Check as manager
        $sql = "SELECT COUNT(*) as count FROM interstate_request WHERE status = 'pending' AND current_approval_level = 'manager' AND manager_email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$approver_email]);
        $counts['manager'] = $stmt->fetch(PDO::FETCH_OBJ)->count;
        
        // Check as security manager
        $sql = "SELECT COUNT(*) as count FROM interstate_request WHERE status = 'pending' AND current_approval_level = 'security_manager' AND security_manager_email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$approver_email]);
        $counts['security_manager'] = $stmt->fetch(PDO::FETCH_OBJ)->count;
        
        return $counts;
    }

    /**
     * Get all airlines for dropdown
     * @return array Array of airline objects
     */
    public function getAllAirlinesForDropdown()
    {
        $sql = "SELECT id, name FROM airlines ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get all hotels with state names for dropdown
     * @return array Array of hotel objects
     */
    public function getAllHotelsWithStates()
    {
        $sql = "SELECT h.id, h.name, h.location, h.state_id, s.name as state_name
                FROM hotel h
                JOIN state s ON h.state_id = s.id
                ORDER BY h.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get staff email with name for dropdown
     * @param string $email Staff email
     * @return string Staff name (part before @)
     */
    public function getStaffNameFromEmail($email)
    {
        if (!$email) return '';
        return substr($email, 0, strpos($email, '@'));
    }

    /**
     * Get completed requests count for today
     * @return int Number of completed requests today
     */
    public function getCompletedInterstateRequestsCountToday()
    {
        $sql = "SELECT COUNT(*) as count FROM interstate_request 
                WHERE status = 'completed' 
                AND DATE(updated_at) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count;
    }

    /**
     * Mark trip as completed
     * @param int $id Request ID
     * @return bool True on success, false on failure
     */
    public function markInterstateTripAsCompleted($id)
    {
        $sql = "UPDATE interstate_request SET status = 'completed', updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

///////////////////////////////////////////////////////////////////////////
    //CREATING TRIP REQUEST FUNCTIONS
    /**
     * Get user with department info
     */
    public function getUserWithDepartment($email)
    {
        $sql = "SELECT s.*, d.name as department_name 
                FROM staff_login s
                LEFT JOIN departments d ON s.department_id = d.id
                WHERE s.email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get staff by role
     */
    public function getStaffByRole($role)
    {
        $sql = "SELECT * FROM staff_login WHERE role = :role ORDER BY email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get approved supervisors (users with admin or super_admin role)
     */
    public function getApprovedSupervisors()
    {
        $sql = "SELECT id, email FROM staff_login WHERE role IN ('admin', 'super_admin') ORDER BY email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get states by country for logged in user
     */
    public function getUserStates($user_id)
    {
        $sql = "SELECT s.* 
                FROM state s
                INNER JOIN staff_login st ON st.country_id = s.country_id
                WHERE st.id = :user_id
                ORDER BY s.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get all states (for international trips)
     */
    public function getAllStatesForCountry($country_id)
    {
        $sql = "SELECT * FROM state WHERE country_id = :country_id ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':country_id' => $country_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    /**
     * Get intrastate requests awaiting driver assignment (security_approved with no driver)
     */
    public function getIntrastateRequestsAwaitingDriver()
    {
        $sql = "SELECT ir.*,
                CONCAT(SUBSTRING_INDEX(ir.staff_email, '@', 1)) as staff_name,
                s.name as vehicle_location_state_name, s.code as vehicle_location_code,
                fc.name as funder_code_name,
                CONCAT(SUBSTRING_INDEX(ir.supervisor_email, '@', 1)) as supervisor_name,
                CONCAT(SUBSTRING_INDEX(ir.reviewer_email, '@', 1)) as reviewer_name,
                CONCAT(SUBSTRING_INDEX(ir.co_reviewer_email, '@', 1)) as co_reviewer_name,
                CONCAT(SUBSTRING_INDEX(ir.manager_email, '@', 1)) as manager_name,
                CONCAT(SUBSTRING_INDEX(ir.security_manager_email, '@', 1)) as security_manager_name
                FROM intrastate_request ir
                JOIN state s ON ir.vehicle_location_state_id = s.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                WHERE ir.status = 'security_approved' 
                AND (ir.assigned_driver_id IS NULL OR ir.assigned_driver_id = 0)
                ORDER BY ir.trip_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get intrastate requests in progress (driver assigned but not completed)
     */
    public function getIntrastateRequestsInProgress()
    {
        $sql = "SELECT ir.*,
                CONCAT(SUBSTRING_INDEX(ir.staff_email, '@', 1)) as staff_name,
                s.name as vehicle_location_state_name, s.code as vehicle_location_code,
                fc.name as funder_code_name,
                d.email as driver_email, d.phone as driver_phone,
                CONCAT(SUBSTRING_INDEX(d.email, '@', 1)) as driver_name
                FROM intrastate_request ir
                JOIN state s ON ir.vehicle_location_state_id = s.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                LEFT JOIN driver d ON ir.assigned_driver_id = d.id
                WHERE ir.status = 'security_approved' 
                AND ir.assigned_driver_id IS NOT NULL 
                AND ir.assigned_driver_id != 0
                ORDER BY ir.trip_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get intrastate requests by supervisor (for supervisor dashboard)
     */
    public function getIntrastateRequestsBySupervisor($supervisor_email)
    {
        $sql = "SELECT ir.*,
                CONCAT(SUBSTRING_INDEX(ir.staff_email, '@', 1)) as staff_name,
                s.name as vehicle_location_state_name, s.code as vehicle_location_code,
                fc.name as funder_code_name,
                ir.staff_phone,
                ir.purpose,
                ir.trip_destination,
                ir.trip_date,
                ir.return_date,
                ir.status,
                ir.rejection_reason,
                ir.reviewer_approved_at,
                ir.security_manager_approved_at,
                CASE 
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'reviewer' THEN 'Pending Your Approval'
                    WHEN ir.status = 'pending' AND ir.current_approval_level = 'security_manager' THEN 'With Security Manager'
                    WHEN ir.status = 'security_approved' THEN 'Approved - Awaiting Driver'
                    WHEN ir.status = 'rejected' THEN 'Rejected'
                    WHEN ir.status = 'completed' THEN 'Completed'
                    WHEN ir.status = 'cancelled' THEN 'Cancelled'
                    ELSE ucfirst(ir.status)
                END as approval_status_text
                FROM intrastate_request ir
                JOIN state s ON ir.vehicle_location_state_id = s.id
                JOIN funder_codes fc ON ir.funder_code_id = fc.id
                WHERE ir.supervisor_email = ?
                ORDER BY ir.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$supervisor_email]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Check if user is part of operations team (reviewer, co-reviewer, or manager for any state)
     */
    public function isUserOperationsTeam($user_email)
    {
        $sql = "SELECT COUNT(*) as count FROM ea_state 
                WHERE reviewer_email = ? 
                OR co_reviewer_email = ? 
                OR manager_email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_email, $user_email, $user_email]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count > 0;
    }

    /**
     * Get completed requests count for today
     */
    public function getCompletedRequestsCountToday()
    {
        $sql = "SELECT COUNT(*) as count FROM intrastate_request 
                WHERE status = 'completed' 
                AND DATE(updated_at) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count;
    }

    /**
     * Mark trip as completed
     */
    public function markTripAsCompleted($id)
    {
        $sql = "UPDATE intrastate_request SET status = 'completed', updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Debug method to check what's happening with security_approved trips
     */
    public function debugSecurityApprovedTrips()
    {
        // Simple query to get all security_approved trips
        $sql = "SELECT id, status, assigned_driver_id, trip_destination, staff_email 
                FROM intrastate_request 
                WHERE status = 'security_approved'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        error_log("=== DEBUG: Security Approved Trips ===");
        error_log("Found " . count($results) . " security_approved trips");
        foreach ($results as $row) {
            error_log("Trip ID: {$row->id}, Destination: {$row->trip_destination}, Driver ID: {$row->assigned_driver_id}");
        }
        
        return $results;
    }
    /**
     * Simple query to get awaiting driver trips (no joins)
     */
    public function getSimpleAwaitingDriver()
    {
        $sql = "SELECT * FROM intrastate_request 
                WHERE status = 'security_approved' 
                AND (assigned_driver_id IS NULL OR assigned_driver_id = 0 OR assigned_driver_id = '')
                ORDER BY trip_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Create trip request
     */
    public function createTripRequest($data)
    {
        $sql = "INSERT INTO trip_requests (
                    requester_id, department_id, trip_type, trip_destination, purpose,
                    departure_date, departure_time, vehicle_departure_location, 
                    vehicle_destination_location, return_date, need_driver, 
                    driver_overtime, approved_supervisor_id, reviewer_id, 
                    co_reviewer_id, manager_id, approval_token
                ) VALUES (
                    :requester_id, :department_id, :trip_type, :trip_destination, :purpose,
                    :departure_date, :departure_time, :vehicle_departure_location,
                    :vehicle_destination_location, :return_date, :need_driver,
                    :driver_overtime, :approved_supervisor_id, :reviewer_id,
                    :co_reviewer_id, :manager_id, :approval_token
                )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Get all trip requests for a user
     */
    public function getUserTripRequests($user_id)
    {
        $sql = "SELECT t.*,
                req.email as requester_email,
                req.role as requester_role,
                sup.email as supervisor_email,
                rev.email as reviewer_email,
                co_rev.email as co_reviewer_email,
                mgr.email as manager_email,
                dep.name as department_name,
                dep_loc.name as departure_location_name,
                dest_loc.name as destination_location_name
                FROM trip_requests t
                LEFT JOIN staff_login req ON t.requester_id = req.id
                LEFT JOIN staff_login sup ON t.approved_supervisor_id = sup.id
                LEFT JOIN staff_login rev ON t.reviewer_id = rev.id
                LEFT JOIN staff_login co_rev ON t.co_reviewer_id = co_rev.id
                LEFT JOIN staff_login mgr ON t.manager_id = mgr.id
                LEFT JOIN departments dep ON t.department_id = dep.id
                LEFT JOIN state dep_loc ON t.vehicle_departure_location = dep_loc.id
                LEFT JOIN state dest_loc ON t.vehicle_destination_location = dest_loc.id
                WHERE t.requester_id = :user_id
                ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get pending trips for approval (for reviewers, co-reviewers, managers)
     */
    public function getPendingApprovals($user_id, $user_role)
    {
        $sql = "SELECT t.*,
                req.email as requester_email,
                req.role as requester_role,
                sup.email as supervisor_email,
                rev.email as reviewer_email,
                co_rev.email as co_reviewer_email,
                mgr.email as manager_email,
                dep.name as department_name,
                dep_loc.name as departure_location_name,
                dest_loc.name as destination_location_name
                FROM trip_requests t
                LEFT JOIN staff_login req ON t.requester_id = req.id
                LEFT JOIN staff_login sup ON t.approved_supervisor_id = sup.id
                LEFT JOIN staff_login rev ON t.reviewer_id = rev.id
                LEFT JOIN staff_login co_rev ON t.co_reviewer_id = co_rev.id
                LEFT JOIN staff_login mgr ON t.manager_id = mgr.id
                LEFT JOIN departments dep ON t.department_id = dep.id
                LEFT JOIN state dep_loc ON t.vehicle_departure_location = dep_loc.id
                LEFT JOIN state dest_loc ON t.vehicle_destination_location = dest_loc.id
                WHERE (t.reviewer_id = :user_id 
                    OR t.co_reviewer_id = :user_id 
                    OR t.manager_id = :user_id)
                    AND t.status = 'pending'
                ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Update trip status
     */
    public function updateTripStatus($trip_id, $status, $comments = null)
    {
        $sql = "UPDATE trip_requests SET status = :status, comments = :comments WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $trip_id,
            ':status' => $status,
            ':comments' => $comments
        ]);
    }

    /**
     * Get trip by token
     */
    public function getTripByToken($token)
    {
        $sql = "SELECT t.*,
                req.email as requester_email, req.role as requester_role,
                sup.email as supervisor_email,
                rev.email as reviewer_email,
                co_rev.email as co_reviewer_email,
                mgr.email as manager_email
                FROM trip_requests t
                LEFT JOIN staff_login req ON t.requester_id = req.id
                LEFT JOIN staff_login sup ON t.approved_supervisor_id = sup.id
                LEFT JOIN staff_login rev ON t.reviewer_id = rev.id
                LEFT JOIN staff_login co_rev ON t.co_reviewer_id = co_rev.id
                LEFT JOIN staff_login mgr ON t.manager_id = mgr.id
                WHERE t.approval_token = :token";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get trip requests by supervisor (for supervisor dashboard)
     */
    public function getRequestsBySupervisor($supervisor_id, $status = null)
    {
        $sql = "SELECT t.*,
                req.email as requester_email,
                req.role as requester_role,
                sup.email as supervisor_email,
                rev.email as reviewer_email,
                co_rev.email as co_reviewer_email,
                mgr.email as manager_email,
                dep.name as department_name,
                dep_loc.name as departure_location_name,
                dest_loc.name as destination_location_name
                FROM trip_requests t
                LEFT JOIN staff_login req ON t.requester_id = req.id
                LEFT JOIN staff_login sup ON t.approved_supervisor_id = sup.id
                LEFT JOIN staff_login rev ON t.reviewer_id = rev.id
                LEFT JOIN staff_login co_rev ON t.co_reviewer_id = co_rev.id
                LEFT JOIN staff_login mgr ON t.manager_id = mgr.id
                LEFT JOIN departments dep ON t.department_id = dep.id
                LEFT JOIN state dep_loc ON t.vehicle_departure_location = dep_loc.id
                LEFT JOIN state dest_loc ON t.vehicle_destination_location = dest_loc.id
                WHERE t.approved_supervisor_id = :supervisor_id";
        
        if ($status) {
            $sql .= " AND t.status = :status ORDER BY t.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':supervisor_id' => $supervisor_id, ':status' => $status]);
        } else {
            $sql .= " ORDER BY t.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':supervisor_id' => $supervisor_id]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get all trip requests (for operations)
     */
    public function getAllTripRequests()
    {
        $sql = "SELECT t.*,
                req.email as requester_email,
                req.role as requester_role,
                sup.email as supervisor_email,
                rev.email as reviewer_email,
                co_rev.email as co_reviewer_email,
                mgr.email as manager_email,
                dep.name as department_name,
                dep_loc.name as departure_location_name,
                dest_loc.name as destination_location_name
                FROM trip_requests t
                LEFT JOIN staff_login req ON t.requester_id = req.id
                LEFT JOIN staff_login sup ON t.approved_supervisor_id = sup.id
                LEFT JOIN staff_login rev ON t.reviewer_id = rev.id
                LEFT JOIN staff_login co_rev ON t.co_reviewer_id = co_rev.id
                LEFT JOIN staff_login mgr ON t.manager_id = mgr.id
                LEFT JOIN departments dep ON t.department_id = dep.id
                LEFT JOIN state dep_loc ON t.vehicle_departure_location = dep_loc.id
                LEFT JOIN state dest_loc ON t.vehicle_destination_location = dest_loc.id
                ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get all trip requests by status (for operations)
     */
    public function getAllTripRequestsByStatus($status)
    {
        $sql = "SELECT t.*,
                req.email as requester_email,
                req.role as requester_role,
                sup.email as supervisor_email,
                rev.email as reviewer_email,
                co_rev.email as co_reviewer_email,
                mgr.email as manager_email,
                dep.name as department_name,
                dep_loc.name as departure_location_name,
                dest_loc.name as destination_location_name
                FROM trip_requests t
                LEFT JOIN staff_login req ON t.requester_id = req.id
                LEFT JOIN staff_login sup ON t.approved_supervisor_id = sup.id
                LEFT JOIN staff_login rev ON t.reviewer_id = rev.id
                LEFT JOIN staff_login co_rev ON t.co_reviewer_id = co_rev.id
                LEFT JOIN staff_login mgr ON t.manager_id = mgr.id
                LEFT JOIN departments dep ON t.department_id = dep.id
                LEFT JOIN state dep_loc ON t.vehicle_departure_location = dep_loc.id
                LEFT JOIN state dest_loc ON t.vehicle_destination_location = dest_loc.id
                WHERE t.status = :status
                ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    //////////////////////////////////////////////////////////////////////////////////

}
?>