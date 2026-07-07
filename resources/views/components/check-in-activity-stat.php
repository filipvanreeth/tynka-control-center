<div class="flex flex-col items-center  rounded-xl p-4 text-gray-800 border <?= htmlspecialchars($colors) ?>">
     <h4 class="text-3xl font-bold leading-none"><?= sprintf('%d%s', $total, '<span class="font-normal">x</span>') ?></h4>
     <p class="text-sm leading-none mt-1"><?= $label ?></p>
</div>