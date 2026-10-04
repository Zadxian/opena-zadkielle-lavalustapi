<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
class AuthController extends Controller
{
     public function __construct()
    {
        parent::__construct();
        $this->call->library('api');
    }

    public function create()
    {
        $this->api->require_method('POST');
        $in = $this->api->body();
        $this->db->raw(
            "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())",
            [$in['username'], $in['email'], password_hash($in['password'], PASSWORD_BCRYPT)]
        );
        $this->api->respond(['message' => 'User registered'], 201);
    }

    public function login()
    {
        $this->api->require_method('POST');
        $in   = $this->api->body();
        $stmt = $this->db->raw('SELECT * FROM users WHERE username = ?', [$in['username'] ?? '']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($in['password'] ?? '', $user['password'])) {
            $this->api->respond($this->api->issue_tokens(['id' => $user['id'], 'role' => $user['role']]));
        }
        $this->api->respond_error('Invalid credentials', 401);
    }

    public function logout()
    {
        $this->api->require_method('POST');
        $in = $this->api->body();
        $this->api->revoke_refresh_token($in['refresh_token'] ?? '');
        $this->api->respond(['message' => 'Logged out']);
    }

    public function refresh()
    {
        $this->api->require_method('POST');
        $in = $this->api->body();
        $this->api->refresh_access_token($in['refresh_token'] ?? '');
    }
}
