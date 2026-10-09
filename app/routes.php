<?php
/**
 * RCVXTR — Route definitions
 */

use App\Controllers\StoreController;
use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\AdminController;
use App\Controllers\ApiController;

// ---- Public / Store ----
$router->get('/', [StoreController::class, 'home']);
$router->get('/store', [StoreController::class, 'home']);
$router->get('/store/product/{slug}', [StoreController::class, 'product']);
$router->get('/store/category/{category}', [StoreController::class, 'category']);
$router->post('/store/order', [StoreController::class, 'order']);
$router->post('/store/contact', [StoreController::class, 'contact']);

// ---- Auth ----
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'showForgot']);
$router->post('/forgot-password', [AuthController::class, 'forgot']);

// ---- Client area ----
$router->get('/client', [ClientController::class, 'dashboard']);
$router->get('/client/services', [ClientController::class, 'services']);
$router->get('/client/services/{id}', [ClientController::class, 'serviceDetail']);
$router->get('/client/domains', [ClientController::class, 'domains']);
$router->get('/client/domains/{id}', [ClientController::class, 'domainDetail']);
$router->post('/client/domains/{id}/dns', [ClientController::class, 'domainDns']);
$router->get('/client/invoices', [ClientController::class, 'invoices']);
$router->get('/client/invoices/{id}', [ClientController::class, 'invoiceDetail']);
$router->post('/client/invoices/{id}/pay', [ClientController::class, 'payInvoice']);
$router->get('/client/tickets', [ClientController::class, 'tickets']);
$router->get('/client/tickets/new', [ClientController::class, 'ticketNew']);
$router->post('/client/tickets/new', [ClientController::class, 'ticketCreate']);
$router->get('/client/tickets/{id}', [ClientController::class, 'ticketDetail']);
$router->post('/client/tickets/{id}/reply', [ClientController::class, 'ticketReply']);
$router->get('/client/balance', [ClientController::class, 'balance']);
$router->post('/client/balance/add', [ClientController::class, 'balanceAdd']);
$router->get('/client/api', [ClientController::class, 'apiKeys']);
$router->post('/client/api/create', [ClientController::class, 'apiCreate']);
$router->post('/client/api/{id}/delete', [ClientController::class, 'apiDelete']);
$router->post('/client/api/{id}/toggle', [ClientController::class, 'apiToggle']);
$router->post('/client/api/{id}/ips', [ClientController::class, 'apiIps']);
$router->get('/client/profile', [ClientController::class, 'profile']);
$router->post('/client/profile', [ClientController::class, 'profileSave']);
$router->post('/client/profile/password', [ClientController::class, 'profilePassword']);

// ---- Admin: auth & dashboard ----
$router->get('/admin/login', [AdminController::class, 'showLogin']);
$router->post('/admin/login', [AdminController::class, 'login']);
$router->get('/admin/logout', [AdminController::class, 'logout']);
$router->get('/admin', [AdminController::class, 'dashboard']);

// ---- Admin: clients ----
$router->get('/admin/clients', [AdminController::class, 'clients']);
$router->get('/admin/clients/{id}', [AdminController::class, 'clientDetail']);
$router->post('/admin/clients/{id}', [AdminController::class, 'clientUpdate']);
$router->post('/admin/clients/{id}/delete', [AdminController::class, 'clientDelete']);
$router->post('/admin/clients/{id}/login-as', [AdminController::class, 'loginAs']);
$router->post('/admin/clients/{id}/balance', [AdminController::class, 'clientBalance']);

// ---- Admin: products ----
$router->get('/admin/products', [AdminController::class, 'products']);
$router->get('/admin/products/new', [AdminController::class, 'productNew']);
$router->post('/admin/products/new', [AdminController::class, 'productCreate']);
$router->get('/admin/products/{id}', [AdminController::class, 'productEdit']);
$router->post('/admin/products/{id}', [AdminController::class, 'productUpdate']);
$router->post('/admin/products/{id}/delete', [AdminController::class, 'productDelete']);

// ---- Admin: orders & services ----
$router->get('/admin/orders', [AdminController::class, 'orders']);
$router->get('/admin/orders/{id}', [AdminController::class, 'orderDetail']);
$router->post('/admin/orders/{id}', [AdminController::class, 'orderUpdate']);
$router->get('/admin/services', [AdminController::class, 'services']);
$router->get('/admin/services/{id}', [AdminController::class, 'serviceDetail']);
$router->post('/admin/services/{id}', [AdminController::class, 'serviceUpdate']);

// ---- Admin: invoices ----
$router->get('/admin/invoices', [AdminController::class, 'invoices']);
$router->get('/admin/invoices/new', [AdminController::class, 'invoiceNew']);
$router->post('/admin/invoices/new', [AdminController::class, 'invoiceCreate']);
$router->get('/admin/invoices/{id}', [AdminController::class, 'invoiceDetail']);
$router->post('/admin/invoices/{id}/mark-paid', [AdminController::class, 'invoiceMarkPaid']);
$router->post('/admin/invoices/{id}/delete', [AdminController::class, 'invoiceDelete']);

// ---- Admin: tickets ----
$router->get('/admin/tickets', [AdminController::class, 'tickets']);
$router->get('/admin/tickets/{id}', [AdminController::class, 'ticketDetail']);
$router->post('/admin/tickets/{id}/reply', [AdminController::class, 'ticketReply']);
$router->post('/admin/tickets/{id}/status', [AdminController::class, 'ticketStatus']);

// ---- Admin: domains ----
$router->get('/admin/domains', [AdminController::class, 'domains']);
$router->get('/admin/domains/{id}', [AdminController::class, 'domainDetail']);
$router->post('/admin/domains/{id}', [AdminController::class, 'domainUpdate']);
$router->post('/admin/domains/{id}/delete', [AdminController::class, 'domainDelete']);

// ---- Admin: transactions / payments / modules / settings / reports ----
$router->get('/admin/transactions', [AdminController::class, 'transactions']);
$router->get('/admin/payments', [AdminController::class, 'payments']);
$router->post('/admin/payments/{code}', [AdminController::class, 'paymentsSave']);
$router->get('/admin/modules', [AdminController::class, 'modules']);
$router->post('/admin/modules/{code}', [AdminController::class, 'modulesSave']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings', [AdminController::class, 'settingsSave']);
$router->get('/admin/reports', [AdminController::class, 'reports']);

// ---- API ----
$router->all('/api/v1/{resource}', [ApiController::class, 'handle']);
$router->all('/api/v1/{resource}/{id}', [ApiController::class, 'handle']);

