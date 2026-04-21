<?php
echo $this->render('header');
?>
<div class="full-screen bg-gray-100 min-h-screen pb-8">
    <div class="container mx-auto p-4">
        <div class="my-6 inline-flex gap-1.5 items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-opal-800"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11 5h2"/><path d="M19 12c-.667 5.333 -2.333 8 -5 8h-4c-2.667 0 -4.333 -2.667 -5 -8"/><path d="M11 16c0 .667 .333 1 1 1s1 -.333 1 -1h-2"/><path d="M12 18v2"/><path d="M10 11v.01"/><path d="M14 11v.01"/><path d="M5 4l6 .97l-6.238 6.688a1.021 1.021 0 0 1 -1.41 .111a.953 .953 0 0 1 -.327 -.954l1.975 -6.815"/><path d="M19 4l-6 .97l6.238 6.688c.358 .408 .989 .458 1.41 .111a.953 .953 0 0 0 .327 -.954l-1.975 -6.815"/></svg>
            <h1 class="text-periwinkle-100 uppercase">
                <span class="font-bold">Tynka's</span>
                Care Center
                <span class="text-xs lowercase text-periwinkle-500">(<?= sprintf('v%s', $this->appConfig->appVersion) ?>)</span>
            </h1>
        </div>
        <div id="add-checkin-btn" class="bg-opal-800 text-opal-50 font-bold p-4 rounded-lg w-full cursor-pointer text-center" role="button" tabindex="0" aria-controls="checkin-form-container" aria-expanded="false">
            <div class="inline-flex flex-row items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-auto"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20.942 13.021a9 9 0 1 0 -9.407 7.967"/><path d="M12 7v5l3 3"/><path d="M15 19l2 2l4 -4"/></svg>
                <span class="text-lg">Add Check-In</span>
            </div>
        </div>

        <div
            id="checkin-form-container" class="hidden"><?php echo $this->render('components/check-in-form', [
                'checkInOptions' => $checkInOptions,
                'handlers' => $handlers,
                'flash' => $flash,
            ]) ?>
        </div>
        <div class="mt-12 mb-4">
            <div class="flex flex-row items-center gap-4">
                <h1 class="grow text-2xl font-bold flex flex-row items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-auto mr-1 text-black"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9.615 20h-2.615a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8"/><path d="M14 19l2 2l4 -4"/><path d="M9 8h4"/><path d="M9 12h2"/></svg>
                    <span>Check-ins</span>
                </h1>
                <a href="?t=<?= time(); ?>" shrink-0 class=" text-gray-400 border-b border-gray-400">Refresh</a>
            </div>
            <p class="my-2 text-gray-400 text-sm leading-normal">Tynka's latest check-ins</p>
        </div>
        <div
            class="grid gap-4 mt-4">
            <?php foreach ($checkIns as $checkIn) {
                echo $this->render('components/check-in-card', [
                    'handlerService' => $handlerService,
                    'checkIn' => $checkIn
                ]);
            }
            ?>
            <div class="mx-auto text-center text-gray-400 bg-gray-200 p-1 px-2 rounded text-xs leading-normal"><?= sprintf('Total check-ins: %d', $totalCheckIns) ?></div>
        </div>

        <div class="mt-6 border-t border-gray-200 pt-6">
            <div class="flex flex-row items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="order-2 text-dairy-cream-300 w-8 h-auto"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 18v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2"/><path d="M7 14l3 -3l2 2l3 -3l2 2"/></svg>
                <h2 class="text-2xl font-bold order-1 grow">Statistics</h2>
            </div>
            <p class="mt-1 mb-4 text-sm text-gray-400">Tynka's check-in statistics</p>
        </div>
        <div
            class="grid grid-cols-3 gap-2"><?php
            foreach ($checkInOptionStats as $checkInOptionStat) {
                echo $this->render('components/check-in-option-stat', [
                    'total' => $checkInOptionStat['total'],
                    'label' => $checkInOptionStat['label'],
                    'colors' => $checkInOptionStat['colors']
                ]);
            }
            ?>
        </div>

        <?php echo $this->render('components/top-handlers', [
            'topHandlers' => $topHandlers,
            'handlerService' => $handlerService,
        ]) ?>
    </div>

</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addButton = document.getElementById('add-checkin-btn');
    const formContainer = document.getElementById('checkin-form-container');
    const shouldOpenOnLoad = <?= (!empty($flash) && ($flash['type'] ?? '') === 'error') ? 'true' : 'false' ?>;

    if (!addButton || !formContainer) return;

    const openForm = function () {
        const dateInput = document.getElementById('checkInAt');
        console.log('Date input element:', dateInput);
        console.log('Current value:', dateInput ? dateInput.value : 'N/A');
        
        const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            dateInput.value = now.getFullYear() + '-'
                + pad(now.getMonth() + 1) + '-'
                + pad(now.getDate()) + 'T'
                + pad(now.getHours()) + ':'
                + pad(now.getMinutes());

        formContainer.classList.remove('hidden');
        addButton.classList.add('hidden');
        addButton.setAttribute('aria-expanded', 'true');

        const firstField = formContainer.querySelector('input, select, textarea, button');
        if (firstField) firstField.focus();
    };
    
    const cancelButton = document.getElementById('cancel-checkin-btn');
    
    if (cancelButton) {
        cancelButton.addEventListener('click', function () {
            formContainer.classList.add('hidden');
            addButton.classList.remove('hidden');
            addButton.setAttribute('aria-expanded', 'false');
            addButton.focus();
        });
        cancelButton.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                formContainer.classList.add('hidden');
                addButton.classList.remove('hidden');
                addButton.setAttribute('aria-expanded', 'false');
                addButton.focus();
            }
        });
    }

    addButton.addEventListener('click', openForm);
    addButton.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openForm();
        }
    });

    if (shouldOpenOnLoad) {
        openForm();
    }
});
</script>
<?php
echo $this->render('footer');
