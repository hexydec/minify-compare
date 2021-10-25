<button class="minify__start">Start</button>
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
		<?php foreach ($urls AS $i => $url) { ?>
			<tr data-compare="<?= $i; ?>">
				<td rowspan="2">
					<h3><a href="<?= \htmlspecialchars($url); ?>" target="_blank"><?= \htmlspecialchars($url); ?></a></h3>
				</td>
				<td>Uncompressed</td>
				<td class="minify__input"></td>
				<td class="minify__input-code" rowspan="2"></td>
				<?php
				$n = -1;
				foreach ($minifiers AS $key => $item) { ?>
					<td rowspan="2" class="minify__table-start minify__time"></td>
					<td class="minify__output"></td>
					<td class="minify__diff"></td>
					<td class="minify__ratio"></td>
					<td class="minify__output-code" rowspan="2"></td>
				<?php } ?>
			</tr>
			<tr>
				<td>Gzipped</td>
				<td class="minify__inputgzip"></td>
				<?php foreach ($minifiers AS $key => $item) { ?>
					<td class="minify__outputgzip"></td>
					<td class="minify__diffgzip"></td>
					<td class="minify__ratiogzip"></td>
				<?php } ?>
			</tr>
		<?php } ?>
		<?php foreach (['Total', 'Averages'] AS $item) { ?>
			<tr class="minify__<?= \htmlspecialchars(\strtolower($item)); ?>">
				<td rowspan="2">
					<h3><?= \htmlspecialchars($item); ?></h3>
				</td>
				<td>Uncompressed</td>
				<td class="minify__input"></td>
				<td></td>
				<?php foreach ($minifiers AS $key => $item) { ?>
					<td rowspan="2" class="minify__table-start minify__time"></td>
					<td class="minify__output"></td>
					<td class="minify__diff"></td>
					<td class="minify__ratio"></td>
					<td class="minify__output-code" rowspan="2"></td>
				<?php } ?>
			</tr>
			<tr>
				<td>Gzipped</td>
				<td class="minify__inputgzip"></td>
				<td></td>
				<?php foreach ($minifiers AS $key => $item) { ?>
					<td class="minify__outputgzip"></td>
					<td class="minify__diffgzip"></td>
					<td class="minify__ratiogzip"></td>
				<?php } ?>
			</tr>
		<?php } ?>
	</tbody>
</table>
<script src="../build/minify-compare.js"></script>
