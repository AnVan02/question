<?php
header('Content-Type: application/json');

class EmployeeAPI {
    private $dataFile;
    
    public function __construct() {
        $this->dataFile = __DIR__ . '/data/employees.json';
        $this->ensureDataFileExists();
    }
    
    private function ensureDataFileExists() {
        if (!file_exists(dirname($this->dataFile))) {
            mkdir(dirname($this->dataFile), 0777, true);
        }
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, json_encode([]));
        }
    }
    
    private function readData() {
        $content = file_get_contents($this->dataFile);
        return json_decode($content, true) ?: [];
    }
    
    private function writeData($data) {
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    // Lấy danh sách nhân viên
    public function getEmployees() {
        try {
            $employees = $this->readData();
            return [
                'status' => 'success',
                'data' => $employees
            ];
        } catch(Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Lỗi khi lấy danh sách nhân viên: ' . $e->getMessage()
            ];
        }
    }
    
    // Lấy thông tin một nhân viên
    public function getEmployee($id) {
        try {
            $employees = $this->readData();
            foreach ($employees as $employee) {
                if ($employee['id'] == $id) {
                    return [
                        'status' => 'success',
                        'data' => $employee
                    ];
                }
            }
            return [
                'status' => 'error',
                'message' => 'Không tìm thấy nhân viên'
            ];
        } catch(Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Lỗi khi lấy thông tin nhân viên: ' . $e->getMessage()
            ];
        }
    }
    
    // Thêm nhân viên mới
    public function addEmployee($data) {
        try {
            $employees = $this->readData();
            
            // Tạo ID mới
            $newId = 1;
            if (!empty($employees)) {
                $newId = max(array_column($employees, 'id')) + 1;
            }
            
            $newEmployee = [
                'id' => $newId,
                'ma_nv' => $data['ma_nv'],
                'ten_nv' => $data['ten_nv'],
                'phong_ban' => $data['phong_ban'],
                'chuc_vu' => $data['chuc_vu'],
                'tk_nextcloud' => $data['tk_nextcloud'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $employees[] = $newEmployee;
            $this->writeData($employees);
            
            return [
                'status' => 'success',
                'message' => 'Thêm nhân viên thành công',
                'id' => $newId
            ];
        } catch(Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Lỗi khi thêm nhân viên: ' . $e->getMessage()
            ];
        }
    }
    
    // Cập nhật thông tin nhân viên
    public function updateEmployee($id, $data) {
        try {
            $employees = $this->readData();
            $found = false;
            
            foreach ($employees as &$employee) {
                if ($employee['id'] == $id) {
                    $employee['ma_nv'] = $data['ma_nv'];
                    $employee['ten_nv'] = $data['ten_nv'];
                    $employee['phong_ban'] = $data['phong_ban'];
                    $employee['chuc_vu'] = $data['chuc_vu'];
                    $employee['tk_nextcloud'] = $data['tk_nextcloud'];
                    $employee['updated_at'] = date('Y-m-d H:i:s');
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                $this->writeData($employees);
                return [
                    'status' => 'success',
                    'message' => 'Cập nhật thông tin nhân viên thành công'
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'Không tìm thấy nhân viên'
            ];
        } catch(Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Lỗi khi cập nhật thông tin nhân viên: ' . $e->getMessage()
            ];
        }
    }
    
    // Xóa nhân viên
    public function deleteEmployee($id) {
        try {
            $employees = $this->readData();
            $found = false;
            
            foreach ($employees as $key => $employee) {
                if ($employee['id'] == $id) {
                    unset($employees[$key]);
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                $employees = array_values($employees); // Re-index array
                $this->writeData($employees);
                return [
                    'status' => 'success',
                    'message' => 'Xóa nhân viên thành công'
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'Không tìm thấy nhân viên'
            ];
        } catch(Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Lỗi khi xóa nhân viên: ' . $e->getMessage()
            ];
        }
    }
    
    // Tìm kiếm nhân viên
    public function searchEmployees($keyword) {
        try {
            $employees = $this->readData();
            $keyword = strtolower($keyword);
            $results = [];
            
            foreach ($employees as $employee) {
                if (strpos(strtolower($employee['ma_nv']), $keyword) !== false ||
                    strpos(strtolower($employee['ten_nv']), $keyword) !== false ||
                    strpos(strtolower($employee['phong_ban']), $keyword) !== false ||
                    strpos(strtolower($employee['chuc_vu']), $keyword) !== false) {
                    $results[] = $employee;
                }
            }
            
            return [
                'status' => 'success',
                'data' => $results
            ];
        } catch(Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Lỗi khi tìm kiếm nhân viên: ' . $e->getMessage()
            ];
        }
    }
}

// Xử lý request
$api = new EmployeeAPI();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($method) {
    case 'GET':
        if($action == 'search') {
            $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
            echo json_encode($api->searchEmployees($keyword));
        } else if(isset($_GET['id'])) {
            echo json_encode($api->getEmployee($_GET['id']));
        } else {
            echo json_encode($api->getEmployees());
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if($action == 'update' && isset($_GET['id'])) {
            echo json_encode($api->updateEmployee($_GET['id'], $data));
        } else {
            echo json_encode($api->addEmployee($data));
        }
        break;
        
    case 'DELETE':
        if(isset($_GET['id'])) {
            echo json_encode($api->deleteEmployee($_GET['id']));
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID không được cung cấp'
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Phương thức không được hỗ trợ'
        ]);
        break;
} 