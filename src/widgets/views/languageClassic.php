<?php
/**
 * Created by Navatech.
 * @project Yii2 Multi Language
 * @author  Phuong
 * @email   phuong17889[at]gmail.com
 * @date    13/02/2016
 * @time    16:20 CH
 * @since   1.0.2
 *
 * @var  $data   array
 * @var  $size   int
 */
?>

<div class="navatech-multi-language">
	<div class="language-box">
		<?php foreach ($data as $key => $item) : ?>
			<?php if ($key === 0): ?>
				<div class="flag-box flag-<?= $size ?>">
					<a class="active" href="<?= $item['url'] ?>">
						<span class="flag <?= $item['country'] ?> flag-<?= $size ?>" title="<?= $item['name'] ?>"></span>
					</a>
				</div>
			<?php else : ?>
				<div class="flag-box flag-<?= $size ?>">
					<a href="<?= $item['url'] ?>">
						<span class="flag <?= $item['country'] ?> flag-<?= $size ?>" title="<?= $item['name'] ?>"></span>
					</a>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>