<?php
// Modal to view asset lifecycle events
?>
<div class="modal fade" id="viewLifecycleModal" tabindex="-1" aria-labelledby="viewLifecycleLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="viewLifecycleLabel">
          <i class="bi bi-graph-up-arrow me-2 text-primary"></i>Asset Life Cycle
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <style>
          /* Scoped styles for lifecycle roadmap */
          #lifecycleRoadmap {
            position: relative;
            padding: 18px 6px 6px 6px;
            overflow-x: auto;
            white-space: nowrap;
          }
          .roadmap-track {
            position: relative;
            height: 4px;
            background: #e9ecef;
            border-radius: 4px;
            margin: 8px 0 18px 0;
            min-width: 680px;
          }
          .roadmap-steps {
            display: flex;
            gap: 28px;
            align-items: flex-start;
            position: relative;
            min-width: 680px;
          }
          .roadmap-step {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            min-width: 120px;
          }
          .roadmap-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 3px rgba(13,110,253,.15);
            background: #adb5bd;
          }
          .roadmap-dot.success { background: #198754; }
          .roadmap-dot.primary { background: #0d6efd; }
          .roadmap-dot.info { background: #0dcaf0; }
          .roadmap-dot.warning { background: #ffc107; }
          .roadmap-dot.secondary { background: #6c757d; }
          .roadmap-dot.danger { background: #dc3545; }
          .roadmap-label { font-weight: 600; font-size: .9rem; margin-top: 6px; text-align:center; }
          .roadmap-date { font-size: .8rem; color: #6c757d; }
          .roadmap-ref { font-size: .75rem; color: #6c757d; }
        </style>
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                  <i class="bi bi-hdd-stack text-primary me-2"></i>
                  <div>
                    <div class="small text-muted">Context</div>
                    <div class="fw-semibold" id="lifecycleContext">Assets (items) linked to this entry</div>
                  </div>
                </div>
                <div class="d-flex align-items-center mb-2">
                  <i class="bi bi-card-checklist text-primary me-2"></i>
                  <div>
                    <div class="small text-muted">Events</div>
                    <div class="fw-semibold"><span id="lifecycleCount">0</span> total</div>
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <i class="bi bi-cpu text-primary me-2"></i>
                  <div>
                    <div class="small text-muted">Assets Involved</div>
                    <div class="fw-semibold"><span id="lifecycleAssetsCount">0</span> asset(s)</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-8">
            <div class="alert alert-info py-2 mb-0">
              <i class="bi bi-info-circle me-1"></i>
              This timeline shows movements and status changes across assignments, transfers, red tags, and disposal.
            </div>
          </div>
        </div>

        <!-- Roadmap visualization -->
        <div class="card shadow-sm mb-3">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
              <div class="fw-semibold"><i class="bi bi-signpost-2 me-1 text-primary"></i>Roadmap</div>
              <div class="small text-muted">Scroll horizontally to see the full journey</div>
            </div>
            <div id="lifecycleRoadmap">
              <div class="roadmap-track"></div>
              <div class="roadmap-steps" id="lifecycleRoadmapSteps">
                <!-- Steps injected by JS -->
              </div>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle" id="lifecycleTable">
            <thead class="table-light">
              <tr>
                <th style="width: 180px;">Date</th>
                <th>Event</th>
                <th>From</th>
                <th>To</th>
                <th>Reference</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody id="lifecycleBody">
              <tr>
                <td colspan="6" class="text-center text-muted py-4">No events found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
