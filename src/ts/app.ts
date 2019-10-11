(function() {
    const dir: HTMLElement = <HTMLElement>document.querySelector('.dir');
    dir.addEventListener('click', function() {
        const copyFrom = document.createElement('textarea');
        copyFrom.textContent = <string>dir.dataset.dir;
        
        const bodyElement: HTMLElement = <HTMLElement>document.getElementsByTagName('body')[0];
        bodyElement.appendChild(copyFrom);
        copyFrom.select();
        document.execCommand('copy');
        bodyElement.removeChild(copyFrom);

        window.open();
    }, false);

    const toggle: HTMLElement = <HTMLElement>document.querySelector('span.toggle');
    if (toggle) {
        toggle.addEventListener('click', function() {
            const files: HTMLElement = <HTMLElement>document.querySelector('div.phpfiles');

            if (files.style.display === 'none') {
                toggle.innerHTML = '<span class="icon-chevron-down"></span>';
                files.style.display = 'block';
            } else {
                toggle.innerHTML = '<span class="icon-chevron-right"></span>';
                files.style.display = 'none';
            }
        });
    }
})();