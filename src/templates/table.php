<table class="minify__table">
	<thead>
		<tr>
			<th colspan="3"></th>
			<?php foreach ($minifiers AS $item) { ?>
				<th colspan="5"><?= htmlspecialchars($item); ?></th>
			<?php } ?>
		</tr>
		<tr>
			<th>URL</th>
			<th>Compression</th>
			<th>Input</th>
			<?php foreach ($minifiers AS $item) { ?>
				<th class="minify__table-start">Time</th>
				<th>Output</th>
				<th>Diff</th>
				<th>Ratio</th>
				<th></th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($stats AS $url => $data) {
			$isurl = mb_stripos($url, 'http') === 0; ?>
			<tr<?= $isurl ? '' : ' class="minify__table-totalrow"'; ?>>
				<td rowspan="2">
					<h3>
						<?php if ($isurl) { ?>
							<a href="<?= htmlspecialchars($url); ?>" target="_blank"><?= htmlspecialchars($url); ?></a>
						<?php } else { ?>
							<?= htmlspecialchars($url); ?>
						<?php } ?>
					</h3>
				</td>
				<td>Uncompressed</td>
				<td<?= $data['errors'] ? ' class="minify__table--failed" title="There were '.$data['errors'].' errors in the code"' : ''; ?>><?= number_format($data['input']); ?></td>
				<?php foreach ($data['minifiers'] AS $key => $item) {
					if ($item['irregular'] || $item['errors'] > $data['errors']) {
						$cls = ' class="minify__table--failed"';
					} elseif ($data['best']['output'] === $key) {
						$cls = ' class="minify__table--best"';
					} elseif ($data['worst']['output'] === $key) {
						$cls = ' class="minify__table--worst"';
					} else {
						$cls = '';
					}
					if ($item['errors']) {
						$cls .= ' title="There were '.$item['errors'].' errors in the code"';
					} ?>
					<td rowspan="2" class="minify__table-start<?= $data['best']['time'] === $key ? ' minify__table--best' : ($data['worst']['time'] === $key ? ' minify__table--worst' : ''); ?>"><?= number_format($item['time'], 8); ?></td>
					<td><?= number_format($item['output']); ?></td>
					<td><?= number_format($item['diff']); ?></td>
					<td<?= $cls; ?>><?= number_format($item['ratio'], 2); ?>%</td>
					<td rowspan="2">
						<?php if ($isurl) { ?>
							<a href="?action=code&amp;minifier=<?= htmlspecialchars($key); ?>&amp;url=<?= urlencode($url); ?>" target="_blank" title="View source code">
								<svg width="20" height="20" viewBox="0 0 40 32"><path d="M26 23l3 3 10-10-10-10-3 3 7 7z"></path><path d="M14 9l-3-3-10 10 10 10 3-3-7-7z"></path><path d="M21.916 4.704l2.171 0.592-6 22.001-2.171-0.592 6-22.001z"></path></svg>
							</a>
						<?php } ?>
					</td>
				<?php } ?>
			</tr>
			<tr>
				<td>Gzipped</td>
				<td<?= $data['errors'] ? ' class="minify__table--failed" title="There were '.$data['errors'].' errors in the code"' : ''; ?>><?= number_format($data['inputgzip']); ?></td>
				<?php foreach ($data['minifiers'] AS $key => $item) {
					if ($item['irregular'] || $item['errors'] > $data['errors']) {
						$cls = ' class="minify__table--failed"';
					} elseif ($data['best']['outputgzip'] === $key) {
						$cls = ' class="minify__table--best"';
					} elseif ($data['worst']['outputgzip'] === $key) {
						$cls = ' class="minify__table--worst"';
					} else {
						$cls = '';
					} ?>
					<td><?= number_format($item['outputgzip']); ?></td>
					<td><?= number_format($item['diffgzip']); ?></td>
					<td<?= $cls; ?>><?= number_format($item['ratiogzip'], 2); ?>%</td>
				<?php } ?>
			</tr>
			<?php if (!$isurl) {
				$count = count($stats) - 1; ?>
				<tr>
					<td rowspan="2">
						<h3>Averages</h3>
					</td>
					<td colspan="2">Uncompressed</td>
					<?php foreach ($data['minifiers'] AS $key => $item) { ?>
						<td rowspan="2" class="minify__table-start"><?= number_format($item['time'] / $count, 8); ?></td>
						<td><?= number_format($item['output'] / $count); ?></td>
						<td><?= number_format($item['diff'] / $count); ?>b/ps</td>
						<td><?= number_format(abs($item['diff']) / $item['time']); ?>b/ps</td>
						<td rowspan="2"></td>
					<?php } ?>
				</tr>
				<tr>
					<td colspan="2">Gzipped</td>
					<?php foreach ($data['minifiers'] AS $key => $item) { ?>
						<td><?= number_format($item['outputgzip'] / $count); ?></td>
						<td><?= number_format($item['diffgzip'] / $count); ?>b/ps</td>
						<td><?= number_format(abs($item['diffgzip']) / $item['time']); ?>b/ps</td>
					<?php } ?>
				</tr>
			<?php } ?>
		<?php } ?>
	</tbody>
</table>
