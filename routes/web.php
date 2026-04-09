<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriasController;
use App\Http\Controllers\CategoriasDespesaController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DespesasController;
use App\Http\Controllers\FormasPagamentoController;
use App\Http\Controllers\FornecedoresController;
use App\Http\Controllers\ItensController;
use App\Http\Controllers\MovimentacoesCaixaController;
use App\Http\Controllers\OrcamentosController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VendasController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('signin');
    Route::get('/signin', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::get('/signup', function () {
        return view('pages.auth.signup', ['title' => 'Sign Up']);
    })->name('signup');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/resumo-sistema', function () {
        return view('pages.resumo-sistema', ['title' => 'Resumo do sistema']);
    })->name('resumo-sistema');

    // calender pages
    Route::get('/calendar', function () {
        return view('pages.calender', ['title' => 'Calendar']);
    })->name('calendar');

    // profile pages
    Route::get('/profile', function () {
        return view('pages.profile', ['title' => 'Profile']);
    })->name('profile');

    // form pages
    Route::get('/form-elements', function () {
        return view('pages.form.form-elements', ['title' => 'Form Elements']);
    })->name('form-elements');

    // tables pages
    Route::get('/basic-tables', function () {
        return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
    })->name('basic-tables');

    // pages
    Route::get('/blank', function () {
        return view('pages.blank', ['title' => 'Blank']);
    })->name('blank');

    // error pages
    Route::get('/error-404', function () {
        return view('pages.errors.error-404', ['title' => 'Error 404']);
    })->name('error-404');

    // chart pages
    Route::get('/line-chart', function () {
        return view('pages.chart.line-chart', ['title' => 'Line Chart']);
    })->name('line-chart');

    Route::get('/bar-chart', function () {
        return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
    })->name('bar-chart');

    // ui elements pages
    Route::get('/alerts', function () {
        return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
    })->name('alerts');

    Route::get('/avatars', function () {
        return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
    })->name('avatars');

    Route::get('/badge', function () {
        return view('pages.ui-elements.badges', ['title' => 'Badges']);
    })->name('badges');

    Route::get('/buttons', function () {
        return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
    })->name('buttons');

    Route::get('/image', function () {
        return view('pages.ui-elements.images', ['title' => 'Images']);
    })->name('images');

    Route::get('/videos', function () {
        return view('pages.ui-elements.videos', ['title' => 'Videos']);
    })->name('videos');

    // users CRUD
    Route::resource('usuarios', UsersController::class)->except(['show']);
    Route::put('/usuarios/{usuario}/senha', [UsersController::class, 'updatePassword'])->name('usuarios.update-password');
    Route::resource('categorias', CategoriasController::class)->except(['show']);
    Route::put('/categorias/{categoria}/status', [CategoriasController::class, 'toggleStatus'])->name('categorias.toggle-status');
    Route::resource('itens', ItensController::class)->except(['show']);
    Route::put('/itens/{iten}/status', [ItensController::class, 'toggleStatus'])->name('itens.toggle-status');
    Route::get('clientes/busca', [ClientesController::class, 'busca'])->name('clientes.busca');
    Route::resource('clientes', ClientesController::class)->except(['show']);
    Route::resource('formas-pagamento', FormasPagamentoController::class)->except(['show']);
    Route::resource('categorias-despesa', CategoriasDespesaController::class)->except(['show']);
    Route::resource('fornecedores', FornecedoresController::class)->except(['show']);
    Route::resource('orcamentos', OrcamentosController::class);

    Route::get('vendas/orcamentos/{orcamento}/dados-importacao', [VendasController::class, 'orcamentoParaVenda'])->name('vendas.orcamento-para-venda');
    Route::resource('vendas', VendasController::class);

    Route::resource('despesas', DespesasController::class);

    Route::resource('movimentacoes-caixa', MovimentacoesCaixaController::class);

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
