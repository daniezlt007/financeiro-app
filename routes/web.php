<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
  DashboardController, ClienteController, ServicoController, ProdutoController,
  VendaController, PagamentoController, MetaController, RecorrenciaController,
  ProfileController, EmpresaController, FuncionarioController, UserController,
  TransacaoController, AuditLogController, RelatorioPagamentosController
};

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {
  // Dashboard - acessível por todos os usuários autenticados
  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

  // Rotas para todos os usuários autenticados (admin e funcionários)
  // IMPORTANTE: Rota do recibo ANTES do resource para evitar conflito
  Route::get('/vendas/{venda}/recibo', [VendaController::class, 'recibo'])->name('vendas.recibo');
  Route::resource('vendas', VendaController::class);
  Route::resource('pagamentos', PagamentoController::class);
  Route::get('/financeiro/baixa-lote', [PagamentoController::class, 'baixaLote'])->name('financeiro.baixa-lote');
  Route::post('/financeiro/baixa-lote/processar', [PagamentoController::class, 'processarBaixaLote'])->name('financeiro.baixa-lote.processar');
  Route::get('/clientes/buscar-por-cpf', [ClienteController::class, 'buscarPorCpf'])->name('clientes.buscarPorCpf');
  Route::get('/clientes/buscar-por-nome', [ClienteController::class, 'buscarPorNome'])->name('clientes.buscarPorNome');
  Route::resource('clientes', ClienteController::class);
  Route::resource('servicos', ServicoController::class);
  Route::resource('produtos', ProdutoController::class);
  Route::get('/relatorios/vendas', [\App\Http\Controllers\RelatorioVendasController::class, 'index'])
    ->name('relatorios.vendas');
  Route::get('/relatorios/vendas/export/excel', [\App\Http\Controllers\RelatorioVendasController::class, 'exportExcel'])
    ->name('relatorios.vendas.excel');
  Route::get('/relatorios/vendas/export/pdf', [\App\Http\Controllers\RelatorioVendasController::class, 'exportPdf'])
      ->name('relatorios.vendas.pdf');
  Route::get('/relatorios/pagamentos', [RelatorioPagamentosController::class, 'index'])
    ->name('relatorios.pagamentos');
  Route::get('/relatorios/pagamentos/export/excel', [RelatorioPagamentosController::class, 'exportExcel'])
    ->name('relatorios.pagamentos.excel');
  Route::get('/relatorios/pagamentos/export/pdf', [RelatorioPagamentosController::class, 'exportPdf'])
    ->name('relatorios.pagamentos.pdf');

  // Rotas de funcionários (não admin)
  Route::middleware(['permissions:funcionario'])->group(function () {
    Route::resource('recorrencias', RecorrenciaController::class);
  });

  // Rotas de administrador (apenas admin)
  Route::middleware(['permissions:admin'])->group(function () {
    Route::resource('empresas', EmpresaController::class);
    Route::resource('funcionarios', FuncionarioController::class);
    Route::resource('metas', MetaController::class);
    Route::resource('users', UserController::class);
    
    // Financeiro - apenas admin
    Route::get('/financeiro/dashboard', [TransacaoController::class, 'dashboard'])->name('financeiro.dashboard');
    Route::get('/financeiro/dre-fluxo-caixa', [TransacaoController::class, 'dreFluxoCaixa'])->name('financeiro.dre-fluxo-caixa');
    Route::get('/financeiro/lucro-despesas', [TransacaoController::class, 'lucroDespesas'])->name('financeiro.lucro-despesas');
    Route::get('/financeiro/relatorios', [TransacaoController::class, 'relatorios'])->name('financeiro.relatorios');
    Route::get('/financeiro/relatorios/pdf', [TransacaoController::class, 'gerarPdf'])->name('financeiro.relatorios.pdf');
    Route::resource('financeiro', TransacaoController::class)->parameters(['financeiro' => 'transacao']);
    
    // Logs de Auditoria - apenas admin
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{id}', [AuditLogController::class, 'show'])->name('audit-logs.show');
  });

  // Profile - acessível por todos os usuários autenticados
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
