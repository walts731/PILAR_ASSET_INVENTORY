<div class="modal fade" id="uploadFormatModal" tabindex="-1" aria-labelledby="uploadFormatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="uploadFormatModalLabel">Template Format Guide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Before uploading, please ensure your file includes the following comment blocks:</p>
                <ul class="list-unstyled">
                    <li><code>&lt;!-- HEADER_START --&gt;</code> ... <code>&lt;!-- HEADER_END --&gt;</code></li>
                    <li><code>&lt;!-- SUBHEADER_START --&gt;</code> ... <code>&lt;!-- SUBHEADER_END --&gt;</code></li>
                    <li><code>&lt;!-- FOOTER_START --&gt;</code> ... <code>&lt;!-- FOOTER_END --&gt;</code></li>
                </ul>
                <form id="uploadTemplateForm" action="upload_template.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="template_file" id="templateFileInput" class="form-control mb-2" accept=".docx,.txt,.html" hidden onchange="document.getElementById('uploadTemplateForm').submit();">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" onclick="document.getElementById('templateFileInput').click();">Continue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>