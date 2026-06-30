ALTER TABLE `_tb_wms_clientes_transit_time`
  MODIFY `nome_cliente` VARCHAR(150) NULL,
  ADD COLUMN `zona_partida` VARCHAR(50) NULL AFTER `nome_cliente`,
  ADD COLUMN `zona_transporte` VARCHAR(50) NULL AFTER `cidade`,
  ADD COLUMN `ciclo_inte` INT NULL AFTER `zona_transporte`,
  ADD INDEX `_tb_wms_clientes_transit_time_zona_partida_index` (`zona_partida`),
  ADD INDEX `_tb_wms_clientes_transit_time_zona_transporte_index` (`zona_transporte`);
