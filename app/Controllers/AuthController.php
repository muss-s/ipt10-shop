<?php

namespace App\Controllers;

use App\Models\User;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class AuthController extends Controller
{
    public function showLogin(Request $request): Response
    {
        return $this->view('auth/login', ['title' => 'Log in']);
    }

    public function login(Request $request): Response
    {
        if ($fail = $this->validate($request, ['email' => 'required|email', 'password' => 'required'], '/login')) {
            return $fail;
        }

        $user = User::attempt($request->input('email'), (string) $request->input('password'));
        if (!$user) {
            Session::flash('error', 'Wrong email or password.');
            Session::flash('old', ['email' => $request->input('email')]);
            return $this->redirect('/login');
        }

        $this->signIn($user);
        $to = Session::get('intended', '/');
        Session::forget('intended');
        return $this->redirect($to);
    }

    public function showRegister(Request $request): Response
    {
        return $this->view('auth/register', ['title' => 'Create account']);
    }

    public function register(Request $request): Response
    {
        if ($fail = $this->validate($request, [
            'name' => 'required|max:100',
            'email' => 'required|email|max:150',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ], '/register')) {
            return $fail;
        }

        if (User::findBy('email', strtolower($request->input('email')))) {
            Session::flash('errors', ['email' => 'That email is already registered.']);
            Session::flash('old', $request->only(['name', 'email']));
            return $this->redirect('/register');
        }

        $id = User::register($request->input('name'), $request->input('email'), (string) $request->input('password'));
        $this->signIn(User::find($id));
        Session::flash('success', 'Welcome! Your account is ready.');
        return $this->redirect('/products');
    }

    public function logout(Request $request): Response
    {
        $cart = Session::get('cart');
        Session::forget('user');
        Session::regenerate();
        Session::set('cart', $cart);
        return $this->redirect('/');
    }

    private function signIn(array $user): void
    {
        // New session id on login prevents session fixation.
        Session::regenerate();
        Session::set('user', [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ]);
        Session::flash('success', 'Hello, ' . $user['name'] . '!');
    }
}
