<?php

require_once 'AppController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../repository/UserRepository.php';


class SecurityController extends AppController
{

    private $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository();
    }

    public function login()
    {
        if ($this->getCurrentUserID() != 0) {
            $url = "http://$_SERVER[HTTP_HOST]";
            return header("Location: {$url}/map");
        }

        $userRepository = new UserRepository();

        if (!$this->isPost()) {
            return $this->render('login');
        }

        $email = $_POST['email'];
        $password = md5($_POST['password']);
        $user = $this->userRepository->getUser($email);

        if (!$user) {
            return $this->render('login', ['messages' => ['User not exist!']]);
        }

        if ($user->getEmail() !== $email) {
            return $this->render('login', ['messages' => ['User with this email not exist!']]);
        }

        if ($user->getPassword() !== $password) {
            return $this->render('login', ['messages' => ['Wrong password!']]);
        }


        $this->setCookie($user->getId(), uniqid());


        $url = "http://$_SERVER[HTTP_HOST]";
        return header("Location: {$url}/map");
    }

    public function register()
    {
        if ($this->getCurrentUserID() != 0) {
            $url = "http://$_SERVER[HTTP_HOST]";
            return header("Location: {$url}/map");
        }
        if (!$this->isPost()) {
            return $this->render('register');
        }


        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirmedPassword = $_POST['confirmedPassword'];
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $phone = $_POST['phone'];
        $role = 1;

        if ($password !== $confirmedPassword) {
            return $this->render('register', ['messages' => ['Please provide proper password']]);
        }

        //TODO hash function
        $user = new User(0,$email, md5($password), $name, $surname, $phone, $role);
        $user->setPhone($phone);

        $this->userRepository->addUser($user);

        return $this->render('login', ['messages' => ['You\'ve been succesfully registrated!']]);
    }

    public function log_out()
    {
        $currID = $this->getCurrentUserID();
        if ($currID == 0) {
            return $this->render('login', ['messages' => ["You're session expired"]]);
        }
        return $this->render('login', ['messages' => [$this->unsetCookie($_COOKIE['user_token'])]]);
    }
}