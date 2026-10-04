<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ProductController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->library('api');
        $this->call->database();

    }

    public function index()
    {
        $this->api->require_jwt();
        $products = $this->db->table('products')->get_all();
        $this->api->respond($products);
    }

    public function store()
    {
        $this->api->require_jwt();
        $this->api->require_method('POST');
        $in = $this->api->body();
        $this->db->raw(
            "INSERT INTO products (product_name, description, price, quantity, created_at) VALUES (?, ?, ?, ?, NOW())",
            [$in['product_name'], $in['description'], $in['price'], $in['quantity']]
        );
        $this->api->respond(['message' => 'Product added'], 201);
    }

    public function update($id)
    {
        $this->api->require_jwt();
        $this->api->require_method('PUT');
        $in = $this->api->body();
        $this->db->raw(
            "UPDATE products SET product_name = ?, description = ?, price = ?, quantity = ? WHERE id = ?",
            [$in['product_name'], $in['description'], $in['price'], $in['quantity'], $id]
        );
        $this->api->respond(['message' => 'Product updated']);
    }

    public function destroy($id)
    {
        $this->api->require_jwt();
        $this->api->require_method('DELETE');
        $this->db->raw("DELETE FROM products WHERE id = ?", [$id]);
        $this->api->respond(['message' => 'Product deleted']);
    }
}
