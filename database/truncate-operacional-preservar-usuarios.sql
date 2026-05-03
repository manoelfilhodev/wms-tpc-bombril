-- Limpeza operacional para iniciar o sistema sem dados de movimento.
-- Preserva:
-- - _tb_usuarios
-- - users
-- - _tb_unidades
-- - migrations

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `_tb_apontamentos_kits`;
TRUNCATE TABLE `_tb_apontamentos_transferencia`;
TRUNCATE TABLE `_tb_armazenagem`;
TRUNCATE TABLE `_tb_categorias`;
TRUNCATE TABLE `_tb_conferencia`;
TRUNCATE TABLE `_tb_contagem_ciclica`;
TRUNCATE TABLE `_tb_contagem_global`;
TRUNCATE TABLE `_tb_contagem_itens`;
TRUNCATE TABLE `_tb_contagem_livre`;
TRUNCATE TABLE `_tb_contagens_drone`;
TRUNCATE TABLE `_tb_containers`;
TRUNCATE TABLE `_tb_demanda`;
TRUNCATE TABLE `_tb_demanda_distribuicoes`;
TRUNCATE TABLE `_tb_demanda_itens`;
TRUNCATE TABLE `_tb_demanda_separadores`;
TRUNCATE TABLE `_tb_demanda_status_history`;
TRUNCATE TABLE `_tb_doca_saida`;
TRUNCATE TABLE `_tb_drones`;
TRUNCATE TABLE `_tb_equipamentos`;
TRUNCATE TABLE `_tb_etiquetas`;
TRUNCATE TABLE `_tb_etiquetas_sep_hydra_metais`;
TRUNCATE TABLE `_tb_expedicao`;
TRUNCATE TABLE `_tb_inventario_ciclico`;
TRUNCATE TABLE `_tb_inventario_fichas`;
TRUNCATE TABLE `_tb_inventario_itens`;
TRUNCATE TABLE `_tb_itens_contagem`;
TRUNCATE TABLE `_tb_kit_etiquetas`;
TRUNCATE TABLE `_tb_kit_montagem`;
TRUNCATE TABLE `_tb_listagem_skus_contagem`;
TRUNCATE TABLE `_tb_materiais`;
TRUNCATE TABLE `_tb_materiais_multipack`;
TRUNCATE TABLE `_tb_movimentacoes`;
TRUNCATE TABLE `_tb_movimentacoes_estoque`;
TRUNCATE TABLE `_tb_movimentos_ativos`;
TRUNCATE TABLE `_tb_notificacoes`;
TRUNCATE TABLE `_tb_pedidos`;
TRUNCATE TABLE `_tb_pedidos_itens`;
TRUNCATE TABLE `_tb_posicoes`;
TRUNCATE TABLE `_tb_posicoes_ocupadas`;
TRUNCATE TABLE `_tb_produtividade_operador`;
TRUNCATE TABLE `_tb_recebimento`;
TRUNCATE TABLE `_tb_recebimento_etiquetas`;
TRUNCATE TABLE `_tb_recebimento_itens`;
TRUNCATE TABLE `_tb_relatorio_mb51`;
TRUNCATE TABLE `_tb_relatorio_mb52`;
TRUNCATE TABLE `_tb_respostas_sugestoes`;
TRUNCATE TABLE `_tb_saldo_estoque`;
TRUNCATE TABLE `_tb_separacao_itens`;
TRUNCATE TABLE `_tb_separacoes`;
TRUNCATE TABLE `_tb_sugestoes_atualizacoes`;
TRUNCATE TABLE `_tb_transferencias`;
TRUNCATE TABLE `_tb_user_logs`;
TRUNCATE TABLE `armazenagems`;
TRUNCATE TABLE `licencas`;
TRUNCATE TABLE `user_invites`;
TRUNCATE TABLE `sessions`;
TRUNCATE TABLE `personal_access_tokens`;
TRUNCATE TABLE `cache`;
TRUNCATE TABLE `cache_locks`;
TRUNCATE TABLE `jobs`;
TRUNCATE TABLE `job_batches`;
TRUNCATE TABLE `failed_jobs`;
TRUNCATE TABLE `password_reset_tokens`;

SET FOREIGN_KEY_CHECKS = 1;
