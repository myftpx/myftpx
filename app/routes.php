<?php
/**
 * RCVXTR — Route definitions
 */

use App\Controllers\StoreController;
use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\AdminController;
use App\Controllers\ApiController;
use App\Controllers\PublicController;

// ---- Public / Store ----
$router->get('/', [StoreController::class, 'home']);
$router->get('/store', [StoreController::class, 'home']);
$router->get('/store/product/{slug}', [StoreController::class, 'product']);
$router->get('/store/category/{category}', [StoreController::class, 'category']);
$router->post('/store/order', [StoreController::class, 'order']);
$router->post('/store/contact', [StoreController::class, 'contact']);

// ---- Public content ----
$router->get('/announcements', [PublicController::class, 'announcements']);
$router->get('/announcements/{id}', [PublicController::class, 'announcement']);
$router->get('/knowledgebase', [PublicController::class, 'knowledgebase']);
$router->get('/knowledgebase/category/{id}', [PublicController::class, 'kbCategory']);
$router->get('/knowledgebase/{id}', [PublicController::class, 'kbArticle']);
$router->get('/domains', [PublicController::class, 'domains']);
$router->get('/domains/search', [PublicController::class, 'domainSearch']);
$router->post('/domains/register', [PublicController::class, 'domainRegister']);
$router->get('/network-status', [PublicController::class, 'networkStatus']);
$router->get('/blog', [PublicController::class, 'blog']);
$router->get('/blog/{slug}', [PublicController::class, 'blogPost']);
$router->get('/api-docs', [PublicController::class, 'apiDocs']);

// ---- Auth ----
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'showForgot']);
$router->post('/forgot-password', [AuthController::class, 'forgot']);
$router->get('/verify-email', [AuthController::class, 'showVerifyEmail']);
$router->post('/verify-email', [AuthController::class, 'verifyEmail']);
$router->get('/verify-login', [AuthController::class, 'showVerifyLogin']);
$router->post('/verify-login', [AuthController::class, 'verifyLogin']);
$router->post('/verify-resend', [AuthController::class, 'resendOtp']);

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

// ---- Client: saved cards, bank transfer & payment logs ----
$router->get('/client/cards', [ClientController::class, 'cards']);
$router->post('/client/cards/add', [ClientController::class, 'cardAdd']);
$router->post('/client/cards/{id}/delete', [ClientController::class, 'cardDelete']);
$router->post('/client/cards/{id}/default', [ClientController::class, 'cardDefault']);
$router->get('/client/payments', [ClientController::class, 'paymentLogs']);
$router->post('/client/invoices/{id}/bank-transfer', [ClientController::class, 'bankTransfer']);
$router->post('/client/services/{id}/autorenew', [ClientController::class, 'serviceAutorenew']);
$router->get('/client/contacts', [ClientController::class, 'contacts']);
$router->post('/client/contacts/add', [ClientController::class, 'contactAdd']);
$router->post('/client/contacts/{id}/delete', [ClientController::class, 'contactDelete']);
$router->post('/client/tickets/{id}/rate', [ClientController::class, 'ticketRate']);
$router->get('/client/quotes', [ClientController::class, 'quotes']);
$router->get('/client/quotes/{id}', [ClientController::class, 'quoteDetail']);
$router->get('/client/affiliate', [ClientController::class, 'affiliate']);
$router->post('/client/affiliate/enable', [ClientController::class, 'affiliateEnable']);
$router->get('/client/downloads', [ClientController::class, 'downloads']);
$router->post('/client/services/{id}/cancel', [ClientController::class, 'serviceCancel']);
$router->post('/client/services/{id}/addon', [ClientController::class, 'serviceAddonAdd']);
$router->post('/client/service-addons/{id}/remove', [ClientController::class, 'serviceAddonRemove']);
$router->get('/client/invoices/{id}/print', [ClientController::class, 'invoicePrint']);

// ---- Admin: auth & dashboard ----
$router->get('/admin/login', [AdminController::class, 'showLogin']);
$router->post('/admin/login', [AdminController::class, 'login']);
$router->get('/admin/verify', [AdminController::class, 'showVerify']);
$router->post('/admin/verify', [AdminController::class, 'verify']);
$router->get('/admin/logout', [AdminController::class, 'logout']);
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/admins', [AdminController::class, 'admins']);
$router->post('/admin/admins/add', [AdminController::class, 'adminAdd']);
$router->post('/admin/admins/{id}/delete', [AdminController::class, 'adminDelete']);

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
$router->post('/admin/settings/test-mail', [AdminController::class, 'testMail']);
$router->get('/admin/reports', [AdminController::class, 'reports']);

// ---- Admin: bank accounts & payment logs ----
$router->get('/admin/bank-accounts', [AdminController::class, 'bankAccounts']);
$router->post('/admin/bank-accounts/add', [AdminController::class, 'bankAccountAdd']);
$router->post('/admin/bank-accounts/{id}/delete', [AdminController::class, 'bankAccountDelete']);
$router->post('/admin/bank-accounts/{id}/toggle', [AdminController::class, 'bankAccountToggle']);
$router->get('/admin/payment-logs', [AdminController::class, 'paymentLogs']);
$router->post('/admin/transactions/{id}/confirm', [AdminController::class, 'transactionConfirm']);
$router->post('/admin/transactions/{id}/deny', [AdminController::class, 'transactionDeny']);

// ---- Admin: content & marketing ----
$router->get('/admin/announcements', [AdminController::class, 'announcements']);
$router->post('/admin/announcements/add', [AdminController::class, 'announcementAdd']);
$router->post('/admin/announcements/{id}/delete', [AdminController::class, 'announcementDelete']);
$router->get('/admin/kb', [AdminController::class, 'kb']);
$router->post('/admin/kb/category/add', [AdminController::class, 'kbCategoryAdd']);
$router->post('/admin/kb/category/{id}/delete', [AdminController::class, 'kbCategoryDelete']);
$router->post('/admin/kb/article/add', [AdminController::class, 'kbArticleAdd']);
$router->post('/admin/kb/article/{id}/delete', [AdminController::class, 'kbArticleDelete']);
$router->get('/admin/tld', [AdminController::class, 'tldPricing']);
$router->post('/admin/tld/add', [AdminController::class, 'tldAdd']);
$router->post('/admin/tld/{id}/delete', [AdminController::class, 'tldDelete']);
$router->get('/admin/addons', [AdminController::class, 'addons']);
$router->post('/admin/addons/add', [AdminController::class, 'addonAdd']);
$router->post('/admin/addons/{id}/delete', [AdminController::class, 'addonDelete']);
$router->get('/admin/promotions', [AdminController::class, 'promotions']);
$router->post('/admin/promotions/add', [AdminController::class, 'promotionAdd']);
$router->post('/admin/promotions/{id}/delete', [AdminController::class, 'promotionDelete']);
$router->get('/admin/email-templates', [AdminController::class, 'emailTemplates']);
$router->post('/admin/email-templates/{code}', [AdminController::class, 'emailTemplateSave']);
$router->get('/admin/predefined-replies', [AdminController::class, 'predefinedReplies']);
$router->post('/admin/predefined-replies/add', [AdminController::class, 'predefinedReplyAdd']);
$router->post('/admin/predefined-replies/{id}/delete', [AdminController::class, 'predefinedReplyDelete']);
$router->get('/admin/blog', [AdminController::class, 'blog']);
$router->post('/admin/blog/category/add', [AdminController::class, 'blogCategoryAdd']);
$router->post('/admin/blog/category/{id}/delete', [AdminController::class, 'blogCategoryDelete']);
$router->post('/admin/blog/post/add', [AdminController::class, 'blogPostAdd']);
$router->post('/admin/blog/post/{id}/delete', [AdminController::class, 'blogPostDelete']);
$router->get('/admin/activity-log', [AdminController::class, 'activityLog']);
$router->get('/admin/netlen', [AdminController::class, 'netlen']);
$router->post('/admin/netlen/save', [AdminController::class, 'netlenSave']);
$router->post('/admin/netlen/sync', [AdminController::class, 'netlenSync']);
$router->post('/admin/netlen/register', [AdminController::class, 'netlenRegister']);
$router->post('/admin/netlen/assign', [AdminController::class, 'netlenAssign']);
$router->post('/admin/netlen/{id}/unassign', [AdminController::class, 'netlenUnassign']);
$router->get('/admin/affiliates', [AdminController::class, 'affiliates']);
$router->post('/admin/affiliates/{id}/payout', [AdminController::class, 'affiliatePayout']);
$router->get('/admin/downloads', [AdminController::class, 'downloads']);
$router->post('/admin/downloads/add', [AdminController::class, 'downloadAdd']);
$router->post('/admin/downloads/{id}/delete', [AdminController::class, 'downloadDelete']);
$router->get('/admin/custom-fields', [AdminController::class, 'customFields']);
$router->post('/admin/custom-fields/add', [AdminController::class, 'customFieldAdd']);
$router->post('/admin/custom-fields/{id}/delete', [AdminController::class, 'customFieldDelete']);
$router->get('/admin/cancellations', [AdminController::class, 'cancellations']);
$router->post('/admin/cancellations/{id}/approve', [AdminController::class, 'cancellationApprove']);
$router->post('/admin/cancellations/{id}/deny', [AdminController::class, 'cancellationDeny']);
$router->get('/admin/mass-mail', [AdminController::class, 'massMail']);
$router->post('/admin/mass-mail/send', [AdminController::class, 'massMailSend']);

// ---- Cron ----
$router->get('/cron/billing', [App\Controllers\CronController::class, 'billing']);

// ---- API ----
$router->all('/api/v1/{resource}', [ApiController::class, 'handle']);
$router->all('/api/v1/{resource}/{id}', [ApiController::class, 'handle']);

