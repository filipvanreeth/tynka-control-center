<div class="">
    <div class="mt-6 border-t border-gray-200 pt-6">
        <div class="flex flex-row items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-periwinkle-500 order-2 w-8 h-auto"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M9.5 5.5a2.5 2.5 0 1 0 5 0a2.5 2.5 0 1 0 -5 0" /><path d="M12 21.368l5.095 -5.096a3.088 3.088 0 1 0 -4.367 -4.367l-.728 .727l-.728 -.727a3.088 3.088 0 1 0 -4.367 4.367l5.095 5.096" /></svg>
            <h2 class="text-2xl font-bold grow order-1">Handlers</h2>
        </div>
        <p class="mt-1 mb-4 text-sm text-gray-400">Total check-ins by handler</p>
    </div>
    <div
        class="bg-white border-2 border-periwinkle-800 p-4 mb-8 rounded-xl">
        <?php if (empty($topHandlers)): ?>
            <p class="text-gray-500 text-sm">No check-ins recorded yet.</p>
        <?php else: ?>
            <ol
                class="flex flex-col gap-2">
                <?php foreach ($topHandlers as $topHandler):
                    $name = htmlspecialchars($topHandler->handler->name, ENT_QUOTES, 'UTF-8');
                    $avatar = !empty($topHandler->handler->avatar)
                        ? $appUrl . '/assets/images/' . $topHandler->handler->avatar
                        : null;
                    ?>
                    <li
                        class="flex items-center gap-4 border-b border-periwinkle-800/25 pb-2 last-of-type:border-0 last-of-type:pb-0">
                        <?php if ($avatar): ?>
                            <img
                            src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $name ?>" class="w-8 h-8 rounded-lg object-cover">
                        <?php else: ?>
                            <div class="w-8 h-8 rounded-lg bg-periwinkle-800 flex items-center justify-center"><?= mb_substr($name, 0, 1) ?></div>
                        <?php endif; ?>
                        <span class="grow font-bold"><?= $name ?></span>
                        <span
                            class="text-periwinkle-100"><?= $topHandler->total ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</div>

