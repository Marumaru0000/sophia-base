document.addEventListener('livewire:load', () => {
    const button = document.querySelector('[wire\\:click="redirectTo"]');
    const radioButtons = document.querySelectorAll('input[name="payment"]');

    // 初期状態
    button.disabled = true;

    // ラジオボタンの変更監視
    radioButtons.forEach(radio => {
        radio.addEventListener('change', () => {
            button.disabled = ![...radioButtons].some(r => r.checked);
        });
    });
});
