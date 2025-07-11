        function format(command) {
            document.execCommand(command, false, null);
            updatePreview();
        }

        function insertSpecial(text) {
            const sel = window.getSelection();
            if (!sel.rangeCount) return;
            const range = sel.getRangeAt(0);
            range.deleteContents();
            range.insertNode(document.createTextNode(text));
            updatePreview();
        }

        function setAlignment(section, alignment) {
            const el = document.getElementById(section);
            if (el) {
                el.style.textAlign = alignment;
                updatePreview();
            }
        }

        function updatePreview() {
            const header = document.getElementById('header');
            const subheader = document.getElementById('subheader');
            const footer = document.getElementById('footer');

            const headerPreview = document.getElementById('headerPreview');
            const subheaderPreview = document.getElementById('subheaderPreview');
            const footerPreview = document.getElementById('footerPreview');

            // Set previews
            headerPreview.innerHTML = parseSpecial(header.innerHTML);
            subheaderPreview.innerHTML = parseSpecial(subheader.innerHTML);
            footerPreview.innerHTML = parseSpecial(footer.innerHTML);

            headerPreview.style.cssText = header.style.cssText;
            subheaderPreview.style.cssText = subheader.style.cssText;
            footerPreview.style.cssText = footer.style.cssText;

            // Set hidden HTML content
            // Wrap with inline style
            document.getElementById('header_hidden').value =
                `<div style="font-family:${header.style.fontFamily}; font-size:${header.style.fontSize}; text-align:${header.style.textAlign};">${header.innerHTML}</div>`;

            document.getElementById('subheader_hidden').value =
                `<div style="font-family:${subheader.style.fontFamily}; font-size:${subheader.style.fontSize}; text-align:${subheader.style.textAlign};">${subheader.innerHTML}</div>`;

            document.getElementById('footer_hidden').value =
                `<div style="font-family:${footer.style.fontFamily}; font-size:${footer.style.fontSize}; text-align:${footer.style.textAlign};">${footer.innerHTML}</div>`;

            // Set font family and size hidden inputs
            document.getElementById('header_font_family').value = header.style.fontFamily || '';
            document.getElementById('header_font_size').value = header.style.fontSize || '';

            document.getElementById('subheader_font_family').value = subheader.style.fontFamily || '';
            document.getElementById('subheader_font_size').value = subheader.style.fontSize || '';

            document.getElementById('footer_font_family').value = footer.style.fontFamily || '';
            document.getElementById('footer_font_size').value = footer.style.fontSize || '';
        }

        updatePreview();


        function parseSpecial(html) {
            return html
                .replace(/\$dynamic_year/g, new Date().getFullYear())
                .replace(/\$dynamic_month/g, new Date().toLocaleString('default', {
                    month: 'long'
                }))
                .replace(/\[blank\]/g, '<span class="blank">&nbsp;</span>');
        }

        function previewImage(event, targetId) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById(targetId).innerHTML = `<img src="${reader.result}" style="height: 60px;">`;
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        updatePreview();

        function toggleCommand(button, command) {
            document.execCommand(command, false, null);
            button.classList.toggle("active"); // Toggle highlight
            updatePreview();
        }

        function setFontSize(size) {
            const activeSection = getActiveSection();
            if (activeSection) {
                activeSection.style.fontSize = size;
                updatePreview();
            }
        }

        function setFontFamily(family) {
            const activeSection = getActiveSection();
            if (activeSection) {
                activeSection.style.fontFamily = family;
                updatePreview();
            }
        }

        // Detect which section the user is editing
        let activeEditable = null;
        document.addEventListener("focusin", function(e) {
            if (e.target.classList.contains("rich-input")) {
                activeEditable = e.target;
            }
        });

        function getActiveSection() {
            return activeEditable;
        }

        function insertTable(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        const table = document.createElement("table");
        table.className = "table table-bordered";
        table.innerHTML = `<tr><td>[blank]</td><td>[blank]</td></tr>`;
        section.appendChild(table);
        updatePreview();
    }
}

function addRow(sectionId) {
    const section = document.getElementById(sectionId);
    const table = section.querySelector("table");
    if (table) {
        const cols = table.rows[0]?.cells.length || 1;
        const newRow = table.insertRow();
        for (let i = 0; i < cols; i++) {
            const cell = newRow.insertCell();
            cell.textContent = "[blank]";
        }
        updatePreview();
    }
}

function addColumn(sectionId) {
    const section = document.getElementById(sectionId);
    const table = section.querySelector("table");
    if (table) {
        for (let i = 0; i < table.rows.length; i++) {
            const cell = table.rows[i].insertCell();
            cell.textContent = "[blank]";
        }
        updatePreview();
    }
}

function toggleTableBorders(sectionId) {
    const section = document.getElementById(sectionId);
    const tables = section.querySelectorAll("table");

    tables.forEach(table => {
        table.classList.toggle("table-bordered");
        updatePreview();
    });
}
