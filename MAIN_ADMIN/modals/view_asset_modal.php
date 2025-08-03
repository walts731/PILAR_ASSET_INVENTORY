<div class="modal fade" id="viewAssetModal" tabindex="-1" aria-labelledby="viewAssetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content shadow border border-dark">
      <div class="modal-body p-4" style="font-family: 'Courier New', Courier, monospace;">
        <div class="border border-2 border-dark rounded p-3">

          <!-- Header: Logo, QR, and GOV LABEL -->
          <div class="d-flex justify-content-between align-items-center mb-2">
            <img id="municipalLogoImg" src="" alt="Municipal Logo" style="height: 70px;">
            <div class="text-center flex-grow-1">
              <h6 class="m-0 text-uppercase fw-bold">Government Property</h6>
            </div>
            <img id="viewQrCode" src="" alt="QR Code" style="height: 70px;">
          </div>

          <hr class="border-dark">

          <!-- Description on Top -->
          <div class="mb-3">
            <p class="mb-1"><strong>Description:</strong> <span id="viewDescription"></span></p>
          </div>

          <!-- Asset Image and Details -->
          <div class="row">
            <!-- Asset Image -->
            <div class="col-5 text-center">
              <label class="form-label fw-bold">Asset Image</label>
              <img id="viewAssetImage" src="" alt="Asset Image" class="img-fluid border border-dark rounded" style="max-height: 150px; object-fit: contain;">
            </div>

            <!-- Asset Info -->
            <div class="col-7">
              <p class="mb-1"><strong>Office:</strong> <span id="viewOfficeName"></span></p>
              <p class="mb-1"><strong>Category:</strong> <span id="viewCategoryName"></span></p>
              <p class="mb-1"><strong>Type:</strong> <span id="viewType"></span></p>
              <p class="mb-1"><strong>Status:</strong> <span id="viewStatus"></span></p>
              <p class="mb-1"><strong>Quantity:</strong> <span id="viewQuantity"></span></p>
              <p class="mb-1"><strong>Unit:</strong> <span id="viewUnit"></span></p>
            </div>
          </div>

          <hr class="border-dark">

          <!-- Dates and Value -->
          <div class="mt-3">
            <p class="mb-1"><strong>Acquisition Date:</strong> <span id="viewAcquisitionDate"></span></p>
            <p class="mb-1"><strong>Last Updated:</strong> <span id="viewLastUpdated"></span></p>
            <p class="mb-1"><strong>Unit Cost:</strong> ₱ <span id="viewValue"></span></p>
            <p class="mb-1"><strong>Total Value:</strong> ₱ <span id="viewTotalValue"></span></p>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.viewAssetBtn').forEach(button => {
  button.addEventListener('click', function () {
    const assetId = this.getAttribute('data-id');

    fetch(`get_asset_details.php?id=${assetId}`)
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          alert(data.error);
          return;
        }

        // Set all fields
        document.getElementById('viewDescription').textContent = data.description;
        document.getElementById('viewOfficeName').textContent = data.office_name;
        document.getElementById('viewCategoryName').textContent = data.category;
        document.getElementById('viewType').textContent = data.type;
        document.getElementById('viewStatus').textContent = data.status;
        document.getElementById('viewQuantity').textContent = data.quantity;
        document.getElementById('viewUnit').textContent = data.unit;
        document.getElementById('viewAcquisitionDate').textContent = data.acquisition_date;
        document.getElementById('viewLastUpdated').textContent = data.last_updated;
        document.getElementById('viewValue').textContent = parseFloat(data.value).toFixed(2);

        //  COMPUTE TOTAL VALUE
        const totalValue = parseFloat(data.value) * parseInt(data.quantity);
        document.getElementById('viewTotalValue').textContent = totalValue.toFixed(2);

        // Images
        document.getElementById('viewAssetImage').src = '../img/assets/' + data.image;
        document.getElementById('municipalLogoImg').src = '../img/logo/' + data.logo;
        document.getElementById('viewQrCode').src = '../img/qrcodes/' + data.qr_code;
      });
  });
});

</script>