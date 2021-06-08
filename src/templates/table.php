<table class="minify__table">
	<thead>
		<tr>
			<th colspan="4"></th>
			<?php foreach ($minifiers AS $item) { ?>
				<th colspan="5"><?= htmlspecialchars($item); ?></th>
			<?php } ?>
		</tr>
		<tr>
			<th>URL</th>
			<th>Compression</th>
			<th>Input</th>
			<th></th>
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
		<?php
		$i = -1;
		foreach ($stats AS $url => $data) {
			$i++;
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
				<td rowspan="2">
					<?php if ($isurl) {
						if ($data['errors'] !== null) { ?>
							<input type="checkbox" name="popup" id="popup-input-<?= $i; ?>" value="" class="minify__popup-switch" />
							<label for="popup-input-<?= $i; ?>" class="minify__popup-label <?= $data['errors'] ? 'icon-cross' : 'icon-tick'; ?>" title="View validation results"></label>
							<div class="minify__popup">
								<div class="minify__popup-inner">
									<label class="minify__popup-close icon-cross" for="popup-input-<?= $i; ?>">Close</label>
									<h2 class="minify__popup-heading">Input Validation</h2>
									<p>
										<a href="<?= htmlspecialchars($url); ?>" target="_blank" rel="noopener"><?= htmlspecialchars($url); ?></a>
									</p>
									<h3 class="minify__popup-subheading">
										The input contained <?= $data['errors'] ? $data['errors'].' error'.($data['errors'] === 1 ? '' : 's') : 'no errors'; ?>
										<a href="<?= htmlspecialchars($url); ?>" target="_blank" title="View source code" class="minify__popup-code icon-code"></a>
									</h3>
									<ol class="minify__popup-output"><li><?= implode('</li><li>', array_map('htmlspecialchars', $data['validator'] ?? [])); ?></li></ol>
								</div>
							</div>
						<?php } else { ?>
							<a href="<?= htmlspecialchars($url); ?>" target="_blank" title="View source code" class="minify__popup-code icon-code"></a>
						<?php }
					} ?>
				</td>
				<?php
				$n = -1;
				foreach ($data['minifiers'] AS $key => $item) {
					$n++;
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
						<?php if ($isurl) {
							if ($item['errors'] !== null) { ?>
								<input type="checkbox" name="popup" id="popup-<?= $i.'-'.$n; ?>" value="" class="minify__popup-switch" />
								<label for="popup-<?= $i.'-'.$n; ?>" class="minify__popup-label <?= $item['errors'] ? 'icon-cross' : 'icon-tick'; ?>" title="View validation results and output code"></label>
								<div class="minify__popup">
									<div class="minify__popup-inner">
										<label class="minify__popup-close icon-cross" for="popup-<?= $i.'-'.$n; ?>">Close</label>
										<h2 class="minify__popup-heading"><?= htmlspecialchars($key); ?></h2>
										<p>
											<a href="<?= htmlspecialchars($url); ?>" target="_blank" rel="noopener"><?= htmlspecialchars($url); ?></a>
										</p>
										<h3 class="minify__popup-subheading">
											The input contained <?= $data['errors'] ? $data['errors'].' error'.($data['errors'] === 1 ? '' : 's') : 'no errors'; ?>, and the output contained <?= $item['errors'] ? $item['errors'].' error'.($item['errors'] === 1 ? '' : 's') : 'no errors'; ?>
											<a href="?action=code&amp;minifier=<?= htmlspecialchars($key); ?>&amp;url=<?= urlencode($url); ?>" target="_blank" title="View source code" class="minify__popup-code icon-code"></a>
										</h3>
										<ol class="minify__popup-output"><li><?= implode('</li><li>', array_map('htmlspecialchars', $item['validator'] ?? [])); ?></li></ol>
									</div>
								</div>
							<?php }
						} ?>
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
					<td colspan="3">Uncompressed</td>
					<?php foreach ($data['minifiers'] AS $key => $item) { ?>
						<td rowspan="2" class="minify__table-start"><?= number_format($item['time'] / $count, 8); ?></td>
						<td><?= number_format($item['output'] / $count); ?></td>
						<td><?= number_format($item['diff'] / $count); ?>b/ps</td>
						<td><?= number_format(abs($item['diff']) / $item['time']); ?>b/ps</td>
						<td rowspan="2"></td>
					<?php } ?>
				</tr>
				<tr>
					<td colspan="3">Gzipped</td>
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
