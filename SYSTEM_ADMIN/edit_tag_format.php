<?php
require_once '../connect.php';
require_once '../includes/tag_format_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$tagHelper = new TagFormatHelper($conn);
$tagType = isset($_GET['type']) ? trim($_GET['type']) : '';
if ($tagType === '') {
    header('Location: manage_tag_format.php');
    exit();
}

// Load the requested tag format
$formats = $tagHelper->getAllTagFormats();
$format = null;
foreach ($formats as $f) {
    if ($f['tag_type'] === $tagType) { $format = $f; break; }
}
if (!$format) {
    $_SESSION['flash'] = ['type'=>'danger','message'=>'Unknown tag type.'];
    header('Location: manage_tag_format.php');
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_format') {
    $data = [
        'format_template' => $_POST['format_template'] ?? $format['format_template'],
        'prefix' => $_POST['prefix'] ?? ($format['prefix'] ?? ''),
        'suffix' => $_POST['suffix'] ?? ($format['suffix'] ?? ''),
        'increment_digits' => (int)($_POST['increment_digits'] ?? ($format['increment_digits'] ?? 4)),
        'date_format' => $_POST['date_format'] ?? ($format['date_format'] ?? '')
    ];

    if ($tagHelper->updateTagFormat($tagType, $data)) {
        $_SESSION['flash'] = ['type'=>'success','message'=>'Tag format updated successfully.'];
    } else {
        $_SESSION['flash'] = ['type'=>'danger','message'=>'Failed to update tag format.'];
    }

    header('Location: edit_tag_format.php?type='.urlencode($tagType));
    exit();
}

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

// For Asset Code testing
$categories = [];
$res_cats = $conn->query("SELECT id, category_name, category_code FROM categories ORDER BY category_name");
if ($res_cats && $res_cats->num_rows > 0) { while ($cr = $res_cats->fetch_assoc()) { $categories[] = $cr; } }
$code_format = $format['tag_type'] === 'asset_code' ? $format['format_template'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Tag Format - <?= htmlspecialchars(strtoupper(str_replace('_',' ', $tagType))) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <style>
    .format-preview{background:#f8f9fa;border:1px solid #dee2e6;border-radius:.375rem;padding:.5rem;font-family:'Courier New',monospace;font-weight:bold;color:#0d6efd}
    .current-format{background:#e7f3ff;border-left:4px solid #0d6efd;padding:1rem;margin-bottom:1rem}
    .format-example-card{transition:transform .2s ease-in-out, box-shadow .2s ease-in-out}
    .format-example-card:hover{transform:translateY(-3px);box-shadow:0 4px 8px rgba(0,0,0,.1)}
  </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
  <?php include 'includes/topbar.php'; ?>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-0"><i class="bi bi-tag"></i> Edit Tag Format: <?= htmlspecialchars(strtoupper(str_replace('_',' ', $tagType))) ?></h4>
        <small class="text-muted">Build and preview your template</small>
      </div>
      <a href="manage_tag_format.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="card shadow-sm">
      <div class="card-body">
        <div class="current-format">
          <small class="text-muted d-block">Current Template</small>
          <div class="format-preview"><?= htmlspecialchars($format['format_template']) ?></div>
          <small class="text-muted d-block mt-1">Next: <strong><?= htmlspecialchars($tagHelper->previewNextTag($format['tag_type'])) ?></strong></small>
        </div>

        <form method="POST" class="tag-format-form">
          <input type="hidden" name="action" value="update_format">
          <input type="hidden" name="tag_type" value="<?= htmlspecialchars($format['tag_type']) ?>">

          <div class="mb-3">
            <label class="form-label">Format Template</label>
            <input type="text" name="format_template" class="form-control" value="<?= htmlspecialchars($format['format_template']) ?>" placeholder="e.g., PAR-{####}" required>
            <div class="placeholder-help mt-1">Use: {####} = Auto-increment (recommended format: PREFIX-{####})</div>

            <div class="card mt-2">
              <div class="card-body py-2">
                <div class="d-flex justify-content-end align-items-center mb-2">
                  <div class="form-check form-switch me-3">
                    <input class="form-check-input" type="checkbox" id="toggleOfficePreview" checked>
                    <label class="form-check-label small" for="toggleOfficePreview">Include OFFICE in preview</label>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="autoDashSwitch_<?= htmlspecialchars($format['tag_type']) ?>" checked>
                    <label class="form-check-label small" for="autoDashSwitch_<?= htmlspecialchars($format['tag_type']) ?>">Auto add dash</label>
                  </div>
                </div>
                <div class="row g-2">
                  <div class="col-12 col-md-6 col-xl-4">
                    <div class="small text-muted mb-1"><i class="bi bi-calendar"></i> Date</div>
                    <div class="d-flex flex-wrap gap-2">
                      <button type="button" class="btn btn-sm btn-outline-primary" onclick="appendTokenToTemplate(this.form, '{YYYY}')">{YYYY}</button>
                      <button type="button" class="btn btn-sm btn-outline-primary" onclick="appendTokenToTemplate(this.form, '{YY}')">{YY}</button>
                      <button type="button" class="btn btn-sm btn-outline-primary" onclick="appendTokenToTemplate(this.form, '{MM}')">{MM}</button>
                      <button type="button" class="btn btn-sm btn-outline-primary" onclick="appendTokenToTemplate(this.form, '{DD}')">{DD}</button>
                      <button type="button" class="btn btn-sm btn-outline-primary" onclick="appendTokenToTemplate(this.form, '{YYYYMM}')">{YYYYMM}</button>
                      <button type="button" class="btn btn-sm btn-outline-primary" onclick="appendTokenToTemplate(this.form, '{YYYYMMDD}')">{YYYYMMDD}</button>
                    </div>
                  </div>
                  <div class="col-12 col-md-6 col-xl-4">
                    <div class="small text-muted mb-1"><i class="bi bi-hash"></i> Digits</div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                      <button type="button" class="btn btn-sm btn-outline-success" onclick="appendTokenToTemplate(this.form, '{#}')">{#}</button>
                      <button type="button" class="btn btn-sm btn-outline-success" onclick="appendTokenToTemplate(this.form, '{##}')">{##}</button>
                      <button type="button" class="btn btn-sm btn-outline-success" onclick="appendTokenToTemplate(this.form, '{###}')">{###}</button>
                      <button type="button" class="btn btn-sm btn-outline-success" onclick="appendTokenToTemplate(this.form, '{####}')">{####}</button>
                      <div class="input-group input-group-sm" style="width:160px;">
                        <span class="input-group-text">#</span>
                        <input type="number" min="1" max="12" class="form-control" placeholder="digits" onkeydown="return event.key !== 'Enter'">
                        <button class="btn btn-outline-success" type="button" onclick="appendCustomDigits(this)">Add</button>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-md-6 col-xl-4">
                    <div class="small text-muted mb-1"><i class="bi bi-plus-square"></i> Other</div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                      <?php if ($format['tag_type'] === 'asset_code'): ?>
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="appendTokenToTemplate(this.form, '{CODE}')">{CODE}</button>
                      <?php else: ?>
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="appendTokenToTemplate(this.form, '{OFFICE}')">{OFFICE}</button>
                      <?php endif; ?>
                      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="appendTokenToTemplate(this.form, '-')">-</button>
                      <div class="input-group input-group-sm" style="width: 220px;">
                        <span class="input-group-text">Text</span>
                        <input type="text" class="form-control" placeholder="literal (e.g., PAR)">
                        <button class="btn btn-outline-secondary" type="button" onclick="appendLiteral(this)">Add</button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mt-2">
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearTemplate(this.form)"><i class="bi bi-x-circle"></i> Clear</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="undoTemplate(this.form)"><i class="bi bi-arrow-counterclockwise"></i> Undo</button>
                </div>
              </div>
            </div>

            <?php if ($format['tag_type'] === 'asset_code'): ?>
            <div class="alert alert-info mt-2 mb-0">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Asset Code Format:</strong> Must include <code>{CODE}</code> placeholder for category codes.
              This format is used in <strong>create_mr.php</strong> for asset creation.
            </div>
            <?php endif; ?>
          </div>

          <div class="mt-3 pt-3 border-top">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Update Format</button>
            <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="previewFormat(this.form)"><i class="bi bi-eye"></i> Preview</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Reuse helper code from original manage_tag_format page
const codeFormatTemplate = <?= json_encode($code_format ?? '') ?>;

function replaceDatePlaceholders(template){
  const now=new Date();
  const y=now.getFullYear().toString();
  const m=String(now.getMonth()+1).padStart(2,'0');
  const d=String(now.getDate()).padStart(2,'0');
  const map={
    '{YYYY}':y,'{YY}':y.slice(-2),'{MM}':m,'{DD}':d,'{MMDD}':m+d,'{YYYYMM}':y+m,'{YYYYMMDD}':y+m+d,
    'YYYY':y,'YY':y.slice(-2),'MM':m,'DD':d,'MMDD':m+d,'YYYYMM':y+m,'YYYYMMDD':y+m+d
  };
  let out=template; for(const [k,v] of Object.entries(map)){ out=out.replace(new RegExp(k,'g'),v);} return out;
}

let includeOfficeInPreview=true; const officePreviewAcronym='MEO';
document.addEventListener('DOMContentLoaded',()=>{ const t=document.getElementById('toggleOfficePreview'); if(t){ includeOfficeInPreview=!!t.checked; t.addEventListener('change',()=>includeOfficeInPreview=!!t.checked); }});
function applyOfficePlaceholderPolicy(t){ let out=t; if(includeOfficeInPreview){ out=out.replace(/\{OFFICE\}|OFFICE/g, officePreviewAcronym);} else { out=out.replace(/-?\{OFFICE\}-?/g,'').replace(/-?OFFICE-?/g,'').replace(/--+/g,'-').replace(/^-|-$/g,''); } return out; }

function previewFormat(templateOrForm){
  let template='';
  if(typeof templateOrForm==='string'){ template=templateOrForm; }
  else if(templateOrForm&&typeof templateOrForm.querySelector==='function'){
    const ti=templateOrForm.querySelector('input[name="format_template"]'); template=ti?String(ti.value||''):'';
  }
  let preview=template; const now=new Date(); const y=now.getFullYear(); const m=String(now.getMonth()+1).padStart(2,'0'); const d=String(now.getDate()).padStart(2,'0');
  preview=preview.replace(/\{YYYY\}|YYYY/g,y).replace(/\{YY\}|YY/g,y.toString().slice(-2)).replace(/\{MM\}|MM/g,m).replace(/\{DD\}|DD/g,d).replace(/\{YYYYMM\}|YYYYMM/g,y+m).replace(/\{YYYYMMDD\}|YYYYMMDD/g,y+m+d);
  preview=preview.replace(/\{(#+)\}/g,(m,hs)=>'0'.repeat(Math.max(1,hs.length-1))+'1');
  preview=preview.replace(/\{CODE\}|CODE/g,'COMP');
  preview=applyOfficePlaceholderPolicy(preview);
  // Inline box
  if(templateOrForm&&typeof templateOrForm.querySelector==='function'){
    let wrap=templateOrForm.querySelector('.inline-preview-wrap'); if(!wrap){ wrap=document.createElement('div'); wrap.className='inline-preview-wrap mt-3'; wrap.innerHTML='<div class="small text-muted mb-1"><i class="bi bi-eye"></i> Preview Result</div><div class="format-preview" style="font-size:1.1em;"><code class="d-block" style="font-size:1.2em; font-weight:bold; color:#0d6efd;"></code></div>'; templateOrForm.appendChild(wrap);} const codeEl=wrap.querySelector('code'); if(codeEl) codeEl.textContent=preview;
  }
}

// Dynamic builder helpers (same as integrated version)
const templateHistories=new Map(); let __tfFormCounter=0;
function ensureFormId(form){ if(!form.dataset.tfbid){ __tfFormCounter+=1; form.dataset.tfbid='tf_'+__tfFormCounter; } return form.dataset.tfbid; }
function getTemplateInput(form){ return form.querySelector('input[name="format_template"]'); }
function getAutoDashEnabled(form){ const sw=form.querySelector('input[id^="autoDashSwitch_"]'); return sw?!!sw.checked:true; }
function pushHistory(form,val){ const id=ensureFormId(form); if(!templateHistories.has(id)) templateHistories.set(id,[]); templateHistories.get(id).push(val); }
function setTemplateValue(form,val){ const i=getTemplateInput(form); if(!i) return; i.value=val; previewFormat(form); }
function getCurrentTemplate(form){ const i=getTemplateInput(form); return i?String(i.value||''):''; }
function appendTokenToTemplate(form,token){ const prev=getCurrentTemplate(form); pushHistory(form,prev); const auto=getAutoDashEnabled(form); let next=prev; const isDash=(token==='-'); if(auto&&!isDash&&prev.length>0){ if(!prev.endsWith('-')) next+='-'; } next+=token; next=next.replace(/-+/g,'-').replace(/^-|-$/g,''); setTemplateValue(form,next); }
function appendCustomDigits(btn){ const grp=btn.closest('.input-group'); const form=btn.closest('form'); if(!grp||!form) return; const inp=grp.querySelector('input[type="number"]'); const n=Math.max(1,Math.min(12, parseInt((inp&&inp.value)?inp.value:'0',10))); if(!n) return; appendTokenToTemplate(form, '{'+ '#'.repeat(n) +'}'); }
function appendLiteral(btn){ const grp=btn.closest('.input-group'); const form=btn.closest('form'); if(!grp||!form) return; const inp=grp.querySelector('input[type="text"]'); const t=inp?inp.value.trim():''; if(!t) return; appendTokenToTemplate(form,t); }
function clearTemplate(form){ const prev=getCurrentTemplate(form); pushHistory(form,prev); setTemplateValue(form,''); }
function undoTemplate(form){ const id=ensureFormId(form); const stack=templateHistories.get(id)||[]; if(stack.length===0) return; const last=stack.pop(); setTemplateValue(form,last); }
</script>
</body>
</html>
