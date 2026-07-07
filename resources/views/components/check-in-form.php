<?php
/** @var $form */
?>

<div class="bg-white p-4 rounded-2xl border-2 border-opal-800">
    <?php if (!empty($flash)):
        $flashStyle = match ($flash['type']) {
            'error' => 'bg-red-100 text-red-500',
            'success' => 'bg-green-100 text-green-500',
            default => ''
        };

        $flashIcon = match ($flash['type']) {
            'error' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="float-left mr-2 text-red-500"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2" /><path d="M12 17l.01 0" /><path d="M12 11l0 3" /></svg>',
            'success' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-green-500 float-left mr-2"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v8" /><path d="M9 18h2" /><path d="M15 19l2 2l4 -4" /></svg>',
            default => ''
        };
        ?>
        <div class="<?= $flashStyle ?> p-4 rounded-lg mb-4"><?= $flashIcon ?>
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>
    <form action="<?= htmlspecialchars($form->action) ?>" method="POST">
        <?php // include __DIR__ . '/../components/handler-avatars.php'; ?>
        <ul class="mb-4 flex flex-col gap-4 text-opal-300">
            <li class="flex flex-col gap-4 border-b border-opal-950 pb-4">
                <label for="handler" class="text-sm text-opal-300">Tynka was cared by</label>
                <select name="handler" id="handler" required
                    class="flex-auto rounded-lg border border-opal-800 bg-opal-950/25 p-4 outline-none appearance-none">
                    <option value="">I'm ...</option>
                    <?php foreach ($form->handlers as $handler):
                        ?>
                        <option value="<?= htmlspecialchars($handler->id) ?>">
                            <?= htmlspecialchars($handler->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </li>
            <li class="flex flex-col gap-4 border-b border-opal-950 pb-4">
                <label for="checkinAt" class="text-sm text-opal-300">At</label>
                <input type="datetime-local" id="checkInAt" name="checkInAt" required
                    class="flex-auto rounded-lg border border-opal-800 bg-opal-950/25 p-4 outline-none" value="<?= isset($form->data->createdAt) && !empty($form->data->createdAt)
                        ? $form->data->createdAt
                        : null;
                    ?>">
            </li>
            <li class="flex flex-col gap-4 border-b border-opal-950 pb-4">
                <label for="activities" class="text-sm text-opal-300">During our walk Tynka</label>
                <?php
                $walkActivities = array_filter(
                    $form->activities,
                    function ($checkInActivity) {
                        return $checkInActivity->category->id === 'walking';
                    }
                );
                ?>
                <div class="inline-flex flex-auto gap-x-4">
                    <?php foreach ($walkActivities as $walkActivity): ?>
                        <div class="inline-flex gap-2 items-center">
                            <input type="checkbox" name="<?= htmlspecialchars($walkActivity->id) ?>" value="1"
                                class="accent-opal-300 w-6 h-6" <?= $form->isActivitySelected($walkActivity) ? 'checked' : '' ?>>
                            <p><?= htmlspecialchars($walkActivity->title) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </li>
            <li class="flex flex-col gap-4 border-b border-opal-950 pb-4">
                <label for="activities" class="text-sm text-opal-300">I gave Tynka her</label>
                <?php
                $careActivities = array_filter(
                    $form->activities,
                    function ($checkInActivity): bool {
                        return $checkInActivity->category->id === 'food';
                    }
                );
                ?>
                <div class="inline-flex gap-x-4">
                    <?php foreach ($careActivities as $careActivity): ?>
                        <div class="inline-flex gap-2 items-center">
                            <input type="checkbox" name="<?= htmlspecialchars($careActivity->id) ?>" value="1"
                                class="accent-opal-300 w-6 h-6" <?= $form->isActivitySelected($careActivity) ? 'checked' : '' ?>>
                            <p><?= htmlspecialchars($careActivity->title) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </li>
        </ul>
        <div class="flex flex-row gap-4">
            <button type="submit"
                class="shrink bg-opal-800 text-opal-50 font-bold p-4 rounded-lg w-full cursor-pointer">
                <div class="inline-flex flex-row items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="w-5 h-auto">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M20.942 13.021a9 9 0 1 0 -9.407 7.967" />
                        <path d="M12 7v5l3 3" />
                        <path d="M15 19l2 2l4 -4" />
                    </svg>
                    <span class="text-lg">Check In</span>
                </div>
            </button>
            <div class="shrink-0 text-center bg-gray-200 flex items-center justify-center rounded-lg px-4 cursor-pointer"
                role="button" tabindex="0" id="cancel-checkin-btn">
                <span class="cursor-pointer px-2 py-4 text-gray-400 whitespace-nowrap"><svg
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                        class="float-left mr-1 h-5 w-auto align-middle">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M21 12a9 9 0 1 0 -9 9" />
                        <path d="M12 7v5l1 1" />
                        <path d="M16 16h6v6h-6l0 -6" />
                    </svg>Cancel</span>
            </div>
        </div>

    </form>
</div>