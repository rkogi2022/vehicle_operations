<?php

class hotel extends Controller
{

    public function __construct()
    {
        parent::__construct();

        session_start();

        if (!isset($_SESSION['user_email'])) {
            header('Location: ' . URL . 'login/index');
            exit();
        }
    }

    public function index()
    {
        $this->hotels();
    }

    // =====================================================
    // DISPLAY HOTELS
    // =====================================================
    public function hotels()
    {
        $hotels = $this->model->getHotelsWithStates();
        $states = $this->model->getAllStates();

        require APP . 'view/_templates/header.php';
        require APP . 'view/hotels/index.php';
    }

    // =====================================================
    // CREATE HOTEL
    // =====================================================
    public function createHotel()
    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $name = trim($_POST['name']);
            $location = trim($_POST['location']);
            $state_id = $_POST['state_id'];

            if (empty($name) || empty($state_id)) {

                $_SESSION['error'] = "Hotel name and state are required";

                header('Location: ' . URL . 'hotel/hotels');
                exit();
            }

            $result = $this->model->addHotel(
                $name,
                $location,
                $state_id
            );

            if ($result) {
                $_SESSION['success'] = "Hotel created successfully";
            } else {
                $_SESSION['error'] = "Failed to create hotel";
            }

            header('Location: ' . URL . 'hotel/hotels');
            exit();
        }

        header('Location: ' . URL . 'hotel/hotels');
        exit();
    }

    // =====================================================
    // EDIT HOTEL
    // =====================================================
    public function editHotel($id)
    {

        $hotel = $this->model->getHotelById($id);

        if (!$hotel) {

            $_SESSION['error'] = "Hotel not found";

            header('Location: ' . URL . 'hotel/hotels');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $name = trim($_POST['name']);
            $location = trim($_POST['location']);
            $state_id = $_POST['state_id'];

            if (empty($name) || empty($state_id)) {

                $_SESSION['error'] = "Hotel name and state are required";

                header('Location: ' . URL . 'hotel/hotels');
                exit();
            }

            $result = $this->model->updateHotel(
                $id,
                $name,
                $location,
                $state_id
            );

            if ($result) {
                $_SESSION['success'] = "Hotel updated successfully";
            } else {
                $_SESSION['error'] = "Failed to update hotel";
            }

            header('Location: ' . URL . 'hotel/hotels');
            exit();
        }

        header('Location: ' . URL . 'hotel/hotels');
        exit();
    }

    // =====================================================
    // DELETE HOTEL
    // =====================================================
    public function deleteHotel($id)
    {

        $result = $this->model->deleteHotel($id);

        if ($result) {
            $_SESSION['success'] = "Hotel deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete hotel";
        }

        header('Location: ' . URL . 'hotel/hotels');
        exit();
    }

    // =====================================================
    // AJAX GET HOTELS BY STATE
    // =====================================================
    public function getHotelsByStateAjax()
    {

        if (isset($_GET['state_id'])) {

            $hotels = $this->model->getHotelsByState($_GET['state_id']);

            header('Content-Type: application/json');

            echo json_encode($hotels);

            exit();
        }

        echo json_encode([]);
        exit();
    }
}
?>