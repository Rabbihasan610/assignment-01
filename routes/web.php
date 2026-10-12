<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DeliveryAssignmentController;
use App\Http\Controllers\Backend\DeliverymanController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\StockAdjustmentController;
use App\Http\Controllers\Backend\UnitController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Delivery\DeliveryController;
use App\Http\Controllers\Delivery\DeliveryDashboardController;
use App\Http\Controllers\Delivery\RoutePlannerController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'welcome'])->name('welcome');


// admin /delivery man / customer are same route for login and logout
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// only register route for customer
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');





Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::middleware(['role:admin'])
        ->prefix('admin')
        ->as('backend.')
        ->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'index'])
                ->name('dashboard');

            Route::resource('units', UnitController::class)
                ->except('show');

            Route::resource('products', ProductController::class)
                ->except('show');

            Route::get('/stock-adjustments', [StockAdjustmentController::class, 'index'])
                ->name('stock-adjustments.index');

            Route::get('/stock-adjustments/create', [StockAdjustmentController::class, 'create'])
                ->name('stock-adjustments.create');

            Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store'])
                ->name('stock-adjustments.store');

            Route::resource('customers', CustomerController::class)
                ->except('show');

            Route::resource('deliverymen', DeliverymanController::class);
            Route::patch(
                'deliverymen/{deliveryman}/toggle-status',
                [DeliverymanController::class, 'toggleStatus']
            )->name('deliverymen.toggleStatus');

            Route::get('/orders', [OrderController::class, 'index'])
                ->name('orders.index');

            Route::get('/orders/{order}', [OrderController::class, 'show'])
                ->name('orders.show');

            Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])
                ->name('orders.updateStatus');

            Route::get('delivery-assignments', [
                DeliveryAssignmentController::class,
                'index',
            ])->name('delivery-assignments.index');

            Route::patch('delivery-assignments/{order}/assign', [
                DeliveryAssignmentController::class,
                'assign',
            ])->name('delivery-assignments.assign');
        });

    Route::middleware(['role:customer'])
        ->prefix('customer')
        ->as('customer.')
        ->group(function () {

            Route::get('/dashboard', [CustomerDashboardController::class, 'index'])
                ->name('dashboard');

            Route::get('/products', [PublicController::class, 'index'])
                ->name('products.index');

            Route::get('/products/{product}', [PublicController::class, 'show'])
                ->name('products.show');


            // add to cart route
            Route::get('/cart', [CartController::class, 'index'])
                ->name('cart.index');
            Route::post('/cart/add', [CartController::class, 'add'])
                ->name('cart.add');

            Route::patch('/cart/{reservation}/update', [CartController::class, 'update'])
                ->name('cart.update');

            Route::delete('/cart/{reservation}/remove', [CartController::class, 'remove'])
                ->name('cart.remove');

            Route::get('/checkout', [CartController::class, 'checkout'])
                ->name('checkout.index');

            Route::post('/checkout', [CartController::class, 'placeOrder'])
                ->name('checkout.store');

            Route::get('/orders', [CustomerOrderController::class, 'index'])
                ->name('orders.index');


            Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])
                ->name('orders.show');

            Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])
                ->name('orders.cancel');
        });

    Route::middleware(['role:delivery_man'])
        ->prefix('deliveryman')
        ->as('deliveryman.')
        ->group(function () {

            Route::get('/dashboard', [DeliveryDashboardController::class, 'index'])->name('dashboard');
            Route::get('/route-planner', [RoutePlannerController::class, 'index'])->name('route-planner');
            Route::post('/route-planner/calculate', [RoutePlannerController::class, 'calculate'])->name('route-planner.calculate');

            Route::get('/assigned-drops', [
                DeliveryController::class,
                'index'
            ])->name('assigned-drops.index');

            Route::get('/assigned-drops/{assignment}', [
                DeliveryController::class,
                'show'
            ])->name('assigned-drops.show');

            Route::post('/assigned-drops/{assignment}/partial-delivery', [
                DeliveryController::class,
                'partialDelivery'
            ])->name('assigned-drops.partial-delivery');

            Route::post('/assigned-drops/{assignment}/complete', [
                DeliveryController::class,
                'completeDelivery'
            ])->name('assigned-drops.complete');

            Route::get('/history', [
                DeliveryController::class,
                'history'
            ])->name('history');
        });
});
