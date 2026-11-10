<?php
/**
 * Скрипты заявки: UTM (CMS) → mail.php payload → иконки/отправка.
 * @var string $lx_service_asset_dir
 */
?>
<script>window.lireateMailSpamGuard=<?php echo json_encode(md5(date('m.Y'))); ?>;</script>
<script src="/template/js/lireate-utm.js?v=20261031"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-lead-mail.js', $lx_service_asset_dir); ?>"></script>
<script src="<?php echo lireate_service_asset_href('assets/lireate-shared/lireate-contact.js', $lx_service_asset_dir); ?>"></script>
