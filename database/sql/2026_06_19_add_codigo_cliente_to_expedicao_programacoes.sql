ALTER TABLE `_tb_expedicao_programacoes`
  ADD COLUMN `codigo_cliente` VARCHAR(50) NULL AFTER `uf_destino`,
  ADD INDEX `idx_exp_prog_codigo_cliente` (`codigo_cliente`);
