<?php

class CartController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = $this->model('Cart');
    }

    public function index($errors = [])
    {
        $session = new Session();

        if ($session->getLogin()) {

            $user_id = $session->getUserId();
            $cart = $this->model->getCart($user_id);

            $data = [
                'titulo' => 'Carrito',
                'menu' => true,
                'user_id' => $user_id,
                'data' => $cart,
                'errors' => $errors
            ];

            $this->view('carts/index', $data);

        } else {
            header('location:' . ROOT);
        }
    }

    public function addProduct($product_id, $user_id)
    {
        $errors = [];

        if ($this->model->verifyProduct($product_id, $user_id) == false) {
            if ($this->model->addProduct($product_id, $user_id) == false) {
                array_push($errors, 'Error al insertar el producto en el carrito');
                header('location:' . ROOT);
            }
        }
        $this->index($errors);
    }

    public function update()
    {
        if (isset($_POST['rows']) && isset($_POST['user_id'])) {
            $errors = [];
            $rows = $_POST['rows'];
            $user_id = $_POST['user_id'];

            for ($i = 0; $i < $rows; $i++) {
                $product_id = $_POST['i'.$i];
                $quantity = $_POST['c'.$i];
                if ( ! $this->model->update($user_id, $product_id, $quantity)) {
                    array_push($errors, 'Error al actualizar el producto');
                    header('location:' . ROOT);
                }
            }
            $this->index($errors);
        }
    }

    public function delete($product, $user)
    {
        $errors = [];

        if( ! $this->model->delete($product, $user)) {
            array_push($errors, 'Error al borrar el registro del carrito');
        }

        $this->index($errors);
    }

    public function checkout()
    {
        $session = new Session();

        if ($session->getLogin()) {

            $user = $session->getUser();

            $data = [
                'titulo' => 'Carrito | Datos de envío',
                'subtitle' => 'Checkout | Verificar dirección de envío',
                'menu' => true,
                'data' => $user,
            ];
            $this->view('carts/address', $data);

        } else {
            $data = [
                'titulo' => 'Carrito | Checkout',
                'subtitle' => 'Checkout | Iniciar sesion',
                'menu' => true
            ];

            $this->view('login', $data);
        }
    }

    public function paymentmode()
    {
        $errors = [];
        $session = new Session();
        if ($session->getLogin()) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                $name = $_POST['first_name'] ?? '';
                $first_name = $_POST['last_name_1'] ?? '';
                $last_name = $_POST['last_name_2']  ?? '';
                $email = $_POST['email'] ?? '';
                $address = $_POST['address'] ?? '';
                $city = $_POST['city'] ?? '';
                $state = $_POST['state'] ?? '';
                $zipcode = $_POST['postcode'] ?? '';
                $country = $_POST['country'] ?? '';

                if ($name == '') {
                    array_push($errors, 'El nombre del usuario es requerido');
                }
                if ($first_name == '') {
                    array_push($errors, 'El primer apellido del usuario es requerido');
                }
                if ($last_name == '') {
                    array_push($errors, 'El segundo apellido del usuario es requerido');
                }
                if ($email == '') {
                    array_push($errors, 'El email es requerido');
                }
                if ($address == '') {
                    array_push($errors, 'La dirección del usuario es requerida');
                }
                if ($city == '') {
                    array_push($errors, 'La ciudad de envio es requerida');
                }
                if ($state == '') {
                    array_push($errors, 'El estado de envio es requerido');
                }
                if ($zipcode == '') {
                    array_push($errors, 'El código postal es requerido');
                }
                if ($country == '') {
                    array_push($errors, 'El país es de envio es requerido');
                }

                if ( ! $errors ) {
                    $data = [
                        'titulo' => 'Carrito | Forma de pago',
                        'subtitle' => 'Checkout | Forma de pago',
                        'menu' => true,
                    ];
                    $this->view('carts/paymentmode', $data);
                }else{
                    $data = [
                        'titulo' => 'Error en los datos de envío',
                        'menu' => false,
                        'errors' => $errors,
                        'subtitle' => 'Error al cotejar los datos de envío',
                        'text' => 'Se ha producido un error durante el proceso de comprobación de los datos de envío',
                        'color' => 'alert-danger',
                        'url' => 'shop',
                        'colorButton' => 'btn-danger',
                        'textButton' => 'Volver',
                    ];
                    $this->view('mensaje', $data);
                }

        } else {
            header('location:' . ROOT);
        }
        }else{
            $this->view('shop/index');
        }

    }

    public function verify()
    {
        $session = new Session();

        if ($session->getLogin()) {
            $user = $session->getUser();
            $cart = $this->model->getCart($user->id);
            $payment = $_POST['payment'] ?? '';

            $data = [
                'titulo' => 'Carrito | Verificar los datos',
                'menu' => true,
                'payment' => $payment,
                'user' => $user,
                'data' => $cart,
            ];

            $this->view('carts/verify', $data);
        }else
        {
            $this->view('shop/index');
        }
    }

    public function thanks()
    {
        $session = new Session();

        if ($session->getLogin()) {
            $user = $session->getUser();

            if ($this->model->closeCart($user->id, 1)) {

                $data = [
                    'titulo' => 'Carrito | Gracias por su compra',
                    'data' => $user,
                    'menu' => true,
                ];

                $this->view('carts/thanks', $data);

            } else {

                $data = [
                    'titulo' => 'Error en la actualización del carrito',
                    'menu' => false,
                    'subtitle' => 'Error en la actualización de los productos del carrito',
                    'text' => 'Existió un problema al actualizar el estado del carrito. Por favor, pruebe más tarde o comuníquese con nuestro servicio de soporte',
                    'color' => 'alert-danger',
                    'url' => 'login',
                    'colorButton' => 'btn-danger',
                    'textButton' => 'Regresar',
                ];

                $this->view('mensaje', $data);

            }
        }else
        {
            $this->view('shop/index');
        }

    }
}