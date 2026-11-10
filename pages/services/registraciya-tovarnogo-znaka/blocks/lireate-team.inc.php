<?php
/**
 * Блок команды: только реальные фото из data/team-members.json.
 * Если enabled=false или нет валидных фото — блок не выводится.
 */
$lx_team_manifest = __DIR__ . '/../data/team-members.json';
if (!is_file($lx_team_manifest)) {
    return;
}
$lx_team_raw = file_get_contents($lx_team_manifest);
if ($lx_team_raw === false) {
    return;
}
$lx_team_data = json_decode($lx_team_raw, true);
if (!is_array($lx_team_data) || empty($lx_team_data['enabled'])) {
    return;
}
$lx_team_members = [];
$lx_team_member_list = isset($lx_team_data['members']) && is_array($lx_team_data['members']) ? $lx_team_data['members'] : array();
foreach ($lx_team_member_list as $lx_member) {
    if (empty($lx_member['name']) || empty($lx_member['photo'])) {
        continue;
    }
    $lx_photo_abs = realpath(__DIR__ . '/../' . ltrim((string) $lx_member['photo'], '/'));
    if ($lx_photo_abs === false || !is_file($lx_photo_abs)) {
        continue;
    }
    $lx_team_members[] = $lx_member;
}
if (count($lx_team_members) === 0) {
    return;
}
$lx_team_heading = isset($lx_team_data['heading']) ? (string) $lx_team_data['heading'] : 'Команда LIREATE';
?>
<section class="lx-section lx-reveal" id="team">
  <span class="lx-section-label">LIREATE</span>
  <h2><?php echo htmlspecialchars($lx_team_heading, ENT_QUOTES, 'UTF-8'); ?></h2>
  <div class="lx-team-grid lx-team-grid--roster">
    <?php foreach ($lx_team_members as $lx_member) : ?>
    <article class="lx-team-card">
      <img
        class="lx-team-photo"
        src="<?php echo htmlspecialchars($lx_member['photo'], ENT_QUOTES, 'UTF-8'); ?>"
        alt="<?php echo htmlspecialchars($lx_member['name'], ENT_QUOTES, 'UTF-8'); ?>"
        width="96"
        height="96"
        loading="eager"
        decoding="async"
      >
      <h3><?php echo htmlspecialchars($lx_member['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
      <p><?php echo htmlspecialchars(isset($lx_member['role']) ? (string) $lx_member['role'] : '', ENT_QUOTES, 'UTF-8'); ?></p>
    </article>
    <?php endforeach; ?>
  </div>
</section>
