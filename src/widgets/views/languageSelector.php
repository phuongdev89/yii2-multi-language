<?php
/**
 * Created by phuongdev89.
 * @project Yii2 Multi Language
 * @author  Phuong
 * @email   phuongdev89@gmail.com
 * @date    13/02/2016
 * @time    16:20 CH
 * @since   1.0.2
 *
 * @var  $data    array
 * @var  $size    int
 * @var  $current array
 */
?>

<div class="phuongdev89-multi-language">
    <div class="dropdown">
        <button class="btn btn-default dropdown-toggle" type="button" id="multiLanguage" data-toggle="dropdown">
            <span class="flag <?= $current['country'] ?> flag-20"></span> <?= $current['name'] ?>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu" aria-labelledby="multiLanguage">
            <?php foreach ($data as $key => $item) : ?>
                <li role="presentation">
                    <?php if ($key === 0): ?>
                        <a class="active" role="menuitem" tabindex="-1" href="<?= $item['url'] ?>">
                            <span class="flag <?= $item['country'] ?> flag-<?= $size ?>"
                                  title="<?= $item['name'] ?>"></span> <?= $item['name'] ?>
                        </a>
                    <?php else : ?>
                        <a role="menuitem" tabindex="-1" href="<?= $item['url'] ?>">
                            <span class="flag <?= $item['country'] ?> flag-<?= $size ?>"
                                  title="<?= $item['name'] ?>"></span> <?= $item['name'] ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
