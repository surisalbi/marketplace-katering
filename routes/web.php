<?php

use App\Http\Controllers\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MerchantController;
use App\Http\Controllers\Admin\UserStatusController;
use App\Http\Controllers\Auth\CustomerRegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MerchantRegisterController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Customer\AlamatController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\InvoiceController;
use App\Http\Controllers\Customer\KateringController;
use App\Http\Controllers\Customer\KeranjangController;
use App\Http\Controllers\Customer\PembayaranController as CustomerPembayaranController;
use App\Http\Controllers\Customer\PesananController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Merchant\DashboardController;
use App\Http\Controllers\Merchant\MenuController;
use App\Http\Controllers\Merchant\PembayaranController as MerchantPembayaranController;
use App\Http\Controllers\Merchant\PesananController as MerchantPesananController;
use App\Http\Controllers\Merchant\ProfileController;
use App\Http\Controllers\PasswordController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(
            Auth::user()->role === 'customer' ? 'customer.dashboard' : 'merchant.dashboard'
        );
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/merchant/register', [MerchantRegisterController::class, 'create'])->name('merchant.register');
    Route::post('/merchant/register', [MerchantRegisterController::class, 'store'])->middleware('throttle:10,1');

    Route::get('/register', [CustomerRegisterController::class, 'create'])->name('customer.register');
    Route::post('/register', [CustomerRegisterController::class, 'store'])->middleware('throttle:10,1');


    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');
});

// OTP (user belum login, dikenali lewat session otp_user_id)
Route::get('/verify-otp', [OtpController::class, 'form'])->name('otp.form');
Route::post('/verify-otp', [OtpController::class, 'verify'])->middleware('throttle:10,1')->name('otp.verify');
Route::post('/resend-otp', [OtpController::class, 'resend'])->middleware('throttle:5,1')->name('otp.resend');

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'merchant'])->prefix('merchant')->name('merchant.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profil', [ProfileController::class, 'update'])->name('profile.update');

    // Menu (hanya merchant terverifikasi)
    Route::middleware('merchant.verified')->group(function () {
        Route::patch('menu/{menu}/toggle', [MenuController::class, 'toggle'])->name('menu.toggle');
        Route::resource('menu', MenuController::class)->except('show');
    });

    Route::get('pesanan', [MerchantPesananController::class, 'index'])->name('pesanan.index');
    Route::get('pesanan/{pesanan}', [MerchantPesananController::class, 'show'])->name('pesanan.show');
    Route::post('pesanan/{pesanan}/status', [MerchantPesananController::class, 'updateStatus'])->name('pesanan.status');

    Route::post('pembayaran/{pembayaran}/verifikasi', [MerchantPembayaranController::class, 'verify'])->name('pembayaran.verify');
    Route::post('pembayaran/{pembayaran}/tolak', [MerchantPembayaranController::class, 'reject'])->name('pembayaran.reject');
    Route::get('pembayaran/{pembayaran}/bukti', [MerchantPembayaranController::class, 'bukti'])->name('pembayaran.bukti');
});

// area customer
Route::middleware(['auth', 'customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [CustomerProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profil', [CustomerProfileController::class, 'update'])->name('profile.update');

    Route::patch('alamat/{alamat}/default', [AlamatController::class, 'setDefault'])->name('alamat.default');
    Route::resource('alamat', AlamatController::class)->except('show');

    Route::get('katering', [KateringController::class, 'index'])->name('katering.index');
    Route::get('katering/{menu}', [KateringController::class, 'show'])->name('katering.show');

    Route::get('keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
    Route::post('keranjang/tambah', [KeranjangController::class, 'add'])->name('keranjang.add');
    Route::post('keranjang/perbarui', [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::post('keranjang/hapus/{menu}', [KeranjangController::class, 'remove'])->name('keranjang.remove');
    Route::post('keranjang/kosongkan', [KeranjangController::class, 'clear'])->name('keranjang.clear');

    Route::get('pesanan', [PesananController::class, 'index'])->name('pesanan.index');
    Route::post('pesanan', [PesananController::class, 'store'])->name('pesanan.store');
    Route::get('pesanan/{pesanan}', [PesananController::class, 'show'])->name('pesanan.show');
    Route::post('pesanan/{pesanan}/batal', [PesananController::class, 'cancel'])->name('pesanan.cancel');

    Route::get('invoice/{invoice}', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('invoice/{invoice}/bayar', [CustomerPembayaranController::class, 'create'])->name('pembayaran.create');
    Route::post('invoice/{invoice}/bayar', [CustomerPembayaranController::class, 'store'])->middleware('throttle:10,1')->name('pembayaran.store');
});

// area admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserStatusController::class, 'index'])->name('users.index');
    Route::patch('/users/{id}/toggle', [UserStatusController::class, 'toggle'])->name('users.toggle');

    Route::get('/merchants', [MerchantController::class, 'index'])->name('merchants.index');
    Route::get('/merchants/{id}', [MerchantController::class, 'show'])->name('merchants.show');
    Route::patch('/merchants/{id}/toggle', [MerchantController::class, 'toggle'])->name('merchants.toggle');
});

// route dengan middleware semua untuk ubah password
Route::middleware(['auth'])->group(function () {
    Route::post('ubah-password', [PasswordController::class, 'update'])->name('ubah-password.update');
});