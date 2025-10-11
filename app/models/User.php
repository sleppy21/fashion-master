<?php
/**
 * MODELO DE USUARIO
 * Maneja todas las operaciones de datos relacionadas con usuarios
 */

class User {
    
    /**
     * Autenticar usuario
     */
    public static function authenticate($username, $password) {
        require_once '../config/conexion.php';
        
        $query = "SELECT * FROM usuario WHERE username_usuario = ?";
        $result = executeQuery($query, [$username]);
        
        if(empty($result)) {
            return false;
        }
        
        $user = $result[0];
        
        // Verificar contraseña con hash
        if(password_verify($password, $user['password_usuario'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Buscar usuario por ID
     */
    public static function findById($id) {
        require_once '../config/conexion.php';
        
        $query = "SELECT * FROM usuario WHERE id_usuario = ?";
        $result = executeQuery($query, [$id]);
        
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Buscar usuario por username
     */
    public static function findByUsername($username) {
        require_once '../config/conexion.php';
        
        $query = "SELECT * FROM usuario WHERE username_usuario = ?";
        $result = executeQuery($query, [$username]);
        
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Buscar usuario por email
     */
    public static function findByEmail($email) {
        require_once '../config/conexion.php';
        
        $query = "SELECT * FROM usuario WHERE email_usuario = ?";
        $result = executeQuery($query, [$email]);
        
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Crear nuevo usuario
     */
    public static function create($data) {
        require_once '../config/conexion.php';
        
        $query = "INSERT INTO usuario (
            username_usuario, 
            email_usuario, 
            password_usuario, 
            nombre_usuario, 
            apellido_usuario, 
            telefono_usuario, 
            rol_usuario
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['nombre'],
            $data['apellido'],
            $data['telefono'] ?? null,
            $data['rol'] ?? 'cliente'
        ];
        
        return executeNonQuery($query, $params);
    }
    
    /**
     * Actualizar usuario
     */
    public static function update($id, $data) {
        require_once '../config/conexion.php';
        
        $fields = [];
        $params = [];
        
        // Construir query dinámicamente según los campos a actualizar
        if(isset($data['username'])) {
            $fields[] = "username_usuario = ?";
            $params[] = $data['username'];
        }
        if(isset($data['email'])) {
            $fields[] = "email_usuario = ?";
            $params[] = $data['email'];
        }
        if(isset($data['password'])) {
            $fields[] = "password_usuario = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if(isset($data['nombre'])) {
            $fields[] = "nombre_usuario = ?";
            $params[] = $data['nombre'];
        }
        if(isset($data['apellido'])) {
            $fields[] = "apellido_usuario = ?";
            $params[] = $data['apellido'];
        }
        if(isset($data['telefono'])) {
            $fields[] = "telefono_usuario = ?";
            $params[] = $data['telefono'];
        }
        if(isset($data['verificado'])) {
            $fields[] = "verificado_usuario = ?";
            $params[] = $data['verificado'];
        }
        
        if(empty($fields)) {
            return false;
        }
        
        $query = "UPDATE usuario SET " . implode(", ", $fields) . " WHERE id_usuario = ?";
        $params[] = $id;
        
        return executeNonQuery($query, $params);
    }
    
    /**
     * Actualizar último acceso
     */
    public static function updateLastAccess($id) {
        require_once '../config/conexion.php';
        
        $query = "UPDATE usuario SET ultimo_acceso = NOW() WHERE id_usuario = ?";
        return executeNonQuery($query, [$id]);
    }
    
    /**
     * Eliminar usuario
     */
    public static function delete($id) {
        require_once '../config/conexion.php';
        
        $query = "DELETE FROM usuario WHERE id_usuario = ?";
        return executeNonQuery($query, [$id]);
    }
    
    /**
     * Obtener todos los usuarios con paginación
     */
    public static function getAll($page = 1, $limit = 20, $role = null) {
        require_once '../config/conexion.php';
        
        $offset = ($page - 1) * $limit;
        
        if($role) {
            $query = "SELECT * FROM usuario WHERE rol_usuario = ? ORDER BY fecha_registro_usuario DESC LIMIT ? OFFSET ?";
            $params = [$role, $limit, $offset];
        } else {
            $query = "SELECT * FROM usuario ORDER BY fecha_registro_usuario DESC LIMIT ? OFFSET ?";
            $params = [$limit, $offset];
        }
        
        return executeQuery($query, $params);
    }
    
    /**
     * Contar usuarios
     */
    public static function count($role = null) {
        require_once '../config/conexion.php';
        
        if($role) {
            $query = "SELECT COUNT(*) as total FROM usuario WHERE rol_usuario = ?";
            $result = executeQuery($query, [$role]);
        } else {
            $query = "SELECT COUNT(*) as total FROM usuario";
            $result = executeQuery($query);
        }
        
        return $result[0]['total'];
    }
    
    /**
     * Verificar si username existe
     */
    public static function usernameExists($username, $exclude_id = null) {
        require_once '../config/conexion.php';
        
        if($exclude_id) {
            $query = "SELECT COUNT(*) as total FROM usuario WHERE username_usuario = ? AND id_usuario != ?";
            $result = executeQuery($query, [$username, $exclude_id]);
        } else {
            $query = "SELECT COUNT(*) as total FROM usuario WHERE username_usuario = ?";
            $result = executeQuery($query, [$username]);
        }
        
        return $result[0]['total'] > 0;
    }
    
    /**
     * Verificar si email existe
     */
    public static function emailExists($email, $exclude_id = null) {
        require_once '../config/conexion.php';
        
        if($exclude_id) {
            $query = "SELECT COUNT(*) as total FROM usuario WHERE email_usuario = ? AND id_usuario != ?";
            $result = executeQuery($query, [$email, $exclude_id]);
        } else {
            $query = "SELECT COUNT(*) as total FROM usuario WHERE email_usuario = ?";
            $result = executeQuery($query, [$email]);
        }
        
        return $result[0]['total'] > 0;
    }
    
    /**
     * Buscar usuarios por término
     */
    public static function search($term, $page = 1, $limit = 20) {
        require_once '../config/conexion.php';
        
        $offset = ($page - 1) * $limit;
        $search_term = "%$term%";
        
        $query = "SELECT * FROM usuario 
                  WHERE username_usuario LIKE ? 
                  OR email_usuario LIKE ? 
                  OR nombre_usuario LIKE ? 
                  OR apellido_usuario LIKE ?
                  ORDER BY fecha_registro_usuario DESC 
                  LIMIT ? OFFSET ?";
        
        return executeQuery($query, [$search_term, $search_term, $search_term, $search_term, $limit, $offset]);
    }
    
    /**
     * Obtener estadísticas de usuarios
     */
    public static function getStats() {
        require_once '../config/conexion.php';
        
        $stats = [];
        
        // Total de usuarios
        $result = executeQuery("SELECT COUNT(*) as total FROM usuario");
        $stats['total'] = $result[0]['total'];
        
        // Por rol
        $result = executeQuery("SELECT rol_usuario, COUNT(*) as total FROM usuario GROUP BY rol_usuario");
        foreach($result as $row) {
            $stats['by_role'][$row['rol_usuario']] = $row['total'];
        }
        
        // Usuarios verificados
        $result = executeQuery("SELECT COUNT(*) as total FROM usuario WHERE verificado_usuario = 1");
        $stats['verified'] = $result[0]['total'];
        
        // Registros hoy
        $result = executeQuery("SELECT COUNT(*) as total FROM usuario WHERE DATE(fecha_registro_usuario) = CURDATE()");
        $stats['today'] = $result[0]['total'];
        
        // Registros esta semana
        $result = executeQuery("SELECT COUNT(*) as total FROM usuario WHERE WEEK(fecha_registro_usuario) = WEEK(NOW())");
        $stats['this_week'] = $result[0]['total'];
        
        return $stats;
    }
}
?>