let activeEditable = null;

            function setAlignment(targetId, alignment) {
                const target = document.getElementById(targetId);
                if (target) {
                    target.style.textAlign = alignment;
                    updatePreview();
                }
            }

            document.addEventListener("focusin", function(e) {
                if (e.target.classList.contains("rich-input")) {
                    activeEditable = e.target;
                }
            });

            function toggleCommand(button, command) {
                document.execCommand(command, false, null);
                button.classList.toggle("active");
                updatePreview();
            }

            function setFontSize(size) {
                if (activeEditable) {
                    activeEditable.style.fontSize = size;
                    updatePreview();
                }
            }

            function setFontFamily(family) {
                if (activeEditable) {
                    activeEditable.style.fontFamily = family;
                    updatePreview();
                }
            }

            function insertSpecial(text) {
                const sel = window.getSelection();
                if (!sel.rangeCount) return;
                const range = sel.getRangeAt(0);
                range.deleteContents();
                range.insertNode(document.createTextNode(text));
                updatePreview();
            }

            function removeLogo(previewId, editorId) {
                document.getElementById(previewId).innerHTML = "";
                document.getElementById(editorId).innerHTML = "";

                if (previewId === 'leftLogoBox') {
                    document.getElementById('remove_left_logo').value = '1';
                }
                if (previewId === 'rightLogoBox') {
                    document.getElementById('remove_right_logo').value = '1';
                }

                updatePreview();
            }

            function previewImage(event, previewId) {
                const reader = new FileReader();
                reader.onload = function() {
                    document.getElementById(previewId).innerHTML = `<img src="${reader.result}" style="height: 60px;">`;
                    updatePreview();
                }
                reader.readAsDataURL(event.target.files[0]);
            }

            function updatePreview() {
                const header = document.getElementById('header');
                const subheader = document.getElementById('subheader');
                const footer = document.getElementById('footer');

                // Use styled wrapper for previews too
                document.getElementById('headerPreview').innerHTML = parseSpecial(wrapWithStyle(header));
                document.getElementById('subheaderPreview').innerHTML = parseSpecial(wrapWithStyle(subheader));
                document.getElementById('footerPreview').innerHTML = parseSpecial(wrapWithStyle(footer));

                // Save to hidden inputs for form submission
                document.getElementById('header_hidden').value = wrapWithStyle(header);
                document.getElementById('subheader_hidden').value = wrapWithStyle(subheader);
                document.getElementById('footer_hidden').value = wrapWithStyle(footer);
            }

            function wrapWithStyle(element) {
                const style = element.getAttribute("style") || "";
                return `<div style="${style}">${element.innerHTML}</div>`;
            }

            function parseSpecial(html) {
                return html
                    .replace(/\$dynamic_year/g, new Date().getFullYear())
                    .replace(/\$dynamic_month/g, new Date().toLocaleString('default', {
                        month: 'long'
                    }))
                    .replace(/\[blank\]/g, '<span class="blank">&nbsp;</span>');
            }

            window.onload = updatePreview;

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
    });

    updatePreview();
}
