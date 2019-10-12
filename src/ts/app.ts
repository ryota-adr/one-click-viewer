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
    function toggleFileList(): void {
        const files: HTMLElement = <HTMLElement>document.querySelector('div.phpfiles');

        if (files.style.display === 'none') {
            toggle.innerHTML = '<span class="icon-chevron-down"></span>';
            files.style.display = 'block';
        } else {
            toggle.innerHTML = '<span class="icon-chevron-right"></span>';
            files.style.display = 'none';
        }
    }

    if (toggle) {
        toggle.addEventListener('click', toggleFileList);
    }

    const targetedElements: HTMLElement[] = [];
    function targetNameChange(): void {
        if (location.hash) {
            const className: string = location.hash.replace('#', '');
            const targetElement: HTMLElement = <HTMLElement>document.querySelector('span.' + className);

            targetedElements.forEach(function (element) {
                element.style.backgroundColor = '';
                element.style.color = '';
            });

            if (!targetedElements.includes(targetElement)) {
                targetedElements.push(targetElement);
            }

            targetElement.style.backgroundColor = '"#e2e6ff';
            targetElement.style.color = '#191919';
        }
    }

    window.onhashchange = targetNameChange;
    targetNameChange();

    const links: NodeList = document.querySelectorAll('[role="link"]');
    const pressedLinks: HTMLElement[] = [];
    
    links.forEach(function (link) {
        link.addEventListener('mouseup', function (event) {
            if ((event as MouseEvent).which === 1 || (event as MouseEvent).which === 2) {
                (link as HTMLElement).style.backgroundColor = '';
                (link as HTMLElement).style.color = '';
            }

            if (!pressedLinks.includes((link as HTMLElement))) {
                pressedLinks.push((link as HTMLElement));
            }

            (link as HTMLElement).style.backgroundColor = '#dcf7d4';
            (link as HTMLElement).style.color = '#191919';
        });
    });

    const toggleButton: HTMLElement = <HTMLElement>document.querySelector('button[role="toggle_input_text"]');
    const inputTextAndButton: HTMLElement = <HTMLElement>document.querySelector('div[role="input_text_and_button"]');
    function toggleInputTextAndButton(event: Event) {
        if (inputTextAndButton.classList.contains('none')) {
            inputTextAndButton.classList.remove('none');
            (event.target as HTMLElement).classList.replace('icon-chevron-right', 'icon-chevron-left');
        } else {
            inputTextAndButton.classList.add('none');
            (event.target as HTMLElement).classList.replace('icon-chevron-left', 'icon-chevron-right');
        }
    }

    toggleButton.addEventListener('click', toggleInputTextAndButton);
})();