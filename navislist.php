<?php

require_once __DIR__ . '/wp-load.php';

/**
 * Kræv login + administratorrettigheder.
 * Hvis ikke: send til forsiden.
 */
if (!is_user_logged_in() || !current_user_can('manage_options')) {
	wp_safe_redirect(home_url('/'));
	exit;
}

$pathinfo = $_SERVER['PATH_INFO'] ?? '';

if (
	!isset($_GET['start']) ||
	!preg_match('/^20\d\d-\d\d-\d\d$/', $_GET['start'])
) {
	echo '<b>/navislist.php?start=20xx-xx-xx</b>';
	exit;
}

$start = $_GET['start'];


/**
 * DATABASE
 */
$db = new \PDO(
	'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
	DB_USER,
	DB_PASSWORD,
	[
		\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_EMULATE_PREPARES   => false,
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
	]
);

global $wpdb;

$users_table    = $wpdb->users;
$usermeta_table = $wpdb->usermeta;
$posts_table    = $wpdb->posts;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Artikler</title>
	</head>
	<body>

<?php

if (
	isset($_GET['pid']) &&
	preg_match('/^\d{1,4}$/', $_GET['pid'])
) {

	$pid = (int) $_GET['pid'];

	$sql = "
		SELECT
			user_login AS uid,
			fn.meta_value AS firstname,
			ln.meta_value AS lastname
		FROM {$users_table} AS users
		JOIN {$usermeta_table} AS fn
			ON fn.user_id = users.ID
		JOIN {$usermeta_table} AS ln
			ON ln.user_id = users.ID
		WHERE users.ID = :pid
			AND fn.meta_key = 'first_name'
			AND ln.meta_key = 'last_name'
	";

	$s = $db->prepare($sql);
	$s->execute([
		':pid' => $pid
	]);

	while (false !== $r = $s->fetch()) {
		$uid       = htmlspecialchars($r['uid'], ENT_QUOTES, 'UTF-8');
		$firstname = htmlspecialchars($r['firstname'], ENT_QUOTES, 'UTF-8');
		$lastname  = htmlspecialchars($r['lastname'], ENT_QUOTES, 'UTF-8');

		echo "<h1 title='{$pid} {$uid}'>{$firstname} {$lastname}</h1>\n";
	}

	$sql = "
		SELECT
			ID AS id,
			post_author,
			post_type,
			post_status,
			post_title
		FROM {$posts_table}
		WHERE post_author = :pid
			AND post_date >= :start
			AND post_status IN ('draft', 'pending', 'publish', 'future')
		ORDER BY post_type DESC
	";

	$s = $db->prepare($sql);
	$s->execute([
		':pid'   => $pid,
		':start' => $start . ' 00:00:00'
	]);

	echo '
	<style>
		tr.values:nth-child(odd) {
			background-color: lightgray;
		}

		td {
			min-width: 110px;
		}

		td.value {
			text-align: center;
		}
	</style>
	';

	echo '<table>';

	while (false !== $r = $s->fetch()) {
		$postType   = htmlspecialchars($r['post_type'], ENT_QUOTES, 'UTF-8');
		$postStatus = htmlspecialchars($r['post_status'], ENT_QUOTES, 'UTF-8');
		$postTitle  = htmlspecialchars($r['post_title'], ENT_QUOTES, 'UTF-8');
		$postId     = (int) $r['id'];

		echo "<tr>\n";
		echo "<td>{$postType}</td>\n";
		echo "<td>{$postStatus}</td>\n";
		echo "<td><a href='/?p={$postId}' target='_blank'>{$postTitle}</a></td>\n";
		echo "</tr>\n";
	}

	echo '</table>';

} else {

	$sql = "
		SELECT
			post_author,
			user_login AS uid,
			fn.meta_value AS firstname,
			ln.meta_value AS lastname,
			post_type,
			post_status,
			COUNT(*) AS count
		FROM {$posts_table}
		JOIN {$users_table} AS users
			ON post_author = users.ID
		JOIN {$usermeta_table} AS fn
			ON post_author = fn.user_id
		JOIN {$usermeta_table} AS ln
			ON post_author = ln.user_id
		WHERE post_date >= :start
			AND post_status IN ('draft', 'pending', 'publish', 'future')
			AND fn.meta_key = 'first_name'
			AND ln.meta_key = 'last_name'
		GROUP BY
			post_author,
			user_login,
			fn.meta_value,
			ln.meta_value,
			post_type,
			post_status
		ORDER BY
			firstname,
			lastname,
			post_type DESC
	";

	$s = $db->prepare($sql);
	$s->execute([
		':start' => $start . ' 00:00:00'
	]);

	$postlist = [];

	while (false !== $r = $s->fetch()) {
		$postlist[$r['uid']]['name'] =
			$r['firstname'] . ' ' . $r['lastname'];

		$postlist[$r['uid']]['pid'] =
			$r['post_author'];

		$postlist[$r['uid']]['types'][$r['post_type']][$r['post_status']] =
			$r['count'];
	}

	echo '
	<style>
		tr.values:nth-child(odd) {
			background-color: lightgray;
		}

		th {
			min-width: 90px;
		}

		td.value {
			text-align: center;
		}
	</style>
	';

	echo '<table>';

	echo '
	<tr>
		<th></th>
		<th></th>
		<th colspan="4">Post<br /></th>
		<th colspan="4">Mediaembed</th>
		<th></th>
	</tr>
	';

	echo '
	<tr>
		<th></th>
		<th></th>
		<th>draft</th>
		<th>pending</th>
		<th>publish</th>
		<th>future</th>
		<th>draft</th>
		<th>pending</th>
		<th>publish</th>
		<th>future</th>
		<th></th>
	</tr>
	';

	foreach ($postlist as $k => $v) {

		$pid  = (int) $v['pid'];
		$name = htmlspecialchars($v['name'], ENT_QUOTES, 'UTF-8');
		$uid  = htmlspecialchars($k, ENT_QUOTES, 'UTF-8');

		$postDraft   = $v['types']['post']['draft'] ?? 0;
		$postPending = $v['types']['post']['pending'] ?? 0;
		$postPublish = $v['types']['post']['publish'] ?? 0;
		$postFuture  = $v['types']['post']['future'] ?? 0;

		$mediaDraft   = $v['types']['mediaembed']['draft'] ?? 0;
		$mediaPending = $v['types']['mediaembed']['pending'] ?? 0;
		$mediaPublish = $v['types']['mediaembed']['publish'] ?? 0;
		$mediaFuture  = $v['types']['mediaembed']['future'] ?? 0;

		echo "<tr class='values'>";

		echo "
			<th>
				<a href='/wp-admin/edit.php?author={$pid}' target='_blank'>
					{$name}
				</a>
			</th>
		";

		echo "<td>{$uid}</td>";

		echo "<td class='value'>{$postDraft}</td>";
		echo "<td class='value'>{$postPending}</td>";
		echo "<td class='value'>{$postPublish}</td>";
		echo "<td class='value'>{$postFuture}</td>";

		echo "<td class='value'>{$mediaDraft}</td>";
		echo "<td class='value'>{$mediaPending}</td>";
		echo "<td class='value'>{$mediaPublish}</td>";
		echo "<td class='value'>{$mediaFuture}</td>";

		echo "
			<td class='value'>
				<a href='/navislist.php?start={$start}&pid={$pid}' target='_blank'>
					vis
				</a>
			</td>
		";

		echo "</tr>";
	}

	echo '</table>';
}

?>

	</body>
</html>