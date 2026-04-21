<?php
/** @var array $handlers */
?>

<ul class="flex gap-4 my-8">
    <?php foreach ($handlers as $handler):
        $handlerAvatar = !empty($handler['avatar'])
            ? APP_URL . '/assets/images/' . $handler['avatar']
            : null;
        ?>
        <li class="flex flex-col items-center">
            <div class="relative">
                <div class="bg-green-500 w-4 h-4 rounded-full absolute right-0 top-0"></div>
                <img src="<?= htmlspecialchars($handlerAvatar) ?>" alt="<?= htmlspecialchars($handler['name']) ?>" class="w-12 h-12 rounded-full object-cover border-2 p-0.5 border-green-500 border-spacing-0.5">
            </div>
            <span class="text-xs font-medium mt-2 text-gray-400"><?= htmlspecialchars($handler['name']) ?></span>
        </li>
    <?php endforeach; ?>
</ul>
