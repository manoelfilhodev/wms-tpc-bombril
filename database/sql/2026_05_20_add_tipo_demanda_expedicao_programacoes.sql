ALTER TABLE `_tb_expedicao_programacoes`
    ADD COLUMN `tipo_demanda` ENUM('PROGRAMADA', 'OPORTUNIDADE') NOT NULL DEFAULT 'PROGRAMADA' AFTER `possui_picking`,
    ADD COLUMN `origem_demanda` VARCHAR(50) NULL AFTER `tipo_demanda`,
    ADD INDEX `idx_exp_prog_tipo_agenda` (`tipo_demanda`, `agenda_entrega_em`);

