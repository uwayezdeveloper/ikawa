(function () {
  "use strict";

  const APP_URL = window.APP_URL || "";

  const locationSelect = document.getElementById("locationSelect");
  if (locationSelect) {
    locationSelect.addEventListener("change", function () {
      const locationId = this.value;
      if (!locationId) return;
      window.location.href = APP_URL + "/finance/station-finances?location_id=" + locationId;
    });
  }

  const payButtons = document.querySelectorAll(".pay-payable-btn");
  const modalElement = document.getElementById("payPayableModal");
  const payableIdInput = document.getElementById("payableId");
  const payableSupplierInput = document.getElementById("payableSupplier");
  const payableRemainingInput = document.getElementById("payableRemaining");
  const payAmountInput = document.getElementById("payAmount");
  const payAccountSelect = document.getElementById("payAccountSelect");
  const payForm = document.getElementById("payPayableForm");

  let modal = null;
  if (modalElement && window.bootstrap && window.bootstrap.Modal) {
    modal = new window.bootstrap.Modal(modalElement);
  }

  payButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      if (!modal) {
        Swal.fire({ icon: "error", title: "Modal Error", text: "Payment modal failed to initialize." });
        return;
      }

      const payableId = this.getAttribute("data-payable-id") || "";
      const supplier = this.getAttribute("data-supplier") || "";
      const remaining = parseFloat(this.getAttribute("data-remaining") || "0") || 0;

      if (payableIdInput) payableIdInput.value = payableId;
      if (payableSupplierInput) payableSupplierInput.value = supplier;
      if (payableRemainingInput) payableRemainingInput.value = remaining.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " FRW";
      if (payAmountInput) {
        payAmountInput.value = remaining.toFixed(2);
        payAmountInput.max = remaining.toFixed(2);
      }
      if (payAccountSelect) payAccountSelect.value = "";

      modal.show();
    });
  });

  if (payForm) {
    payForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const payableId = payableIdInput ? payableIdInput.value : "";
      const accountId = payAccountSelect ? payAccountSelect.value : "";
      const amount = parseFloat(payAmountInput ? payAmountInput.value : "0") || 0;

      const remainingText = payableRemainingInput ? String(payableRemainingInput.value || "") : "0";
      const remaining = parseFloat(remainingText.replace(/[^0-9.]/g, "")) || 0;

      if (!payableId || !accountId || amount <= 0) {
        Swal.fire({ icon: "warning", title: "Missing Data", text: "Select account and enter valid amount." });
        return;
      }

      if (amount > remaining) {
        Swal.fire({ icon: "warning", title: "Invalid Amount", text: "Amount cannot be greater than remaining payable." });
        return;
      }

      const selectedOption = payAccountSelect.options[payAccountSelect.selectedIndex];
      const accountBalance = parseFloat(selectedOption?.dataset?.balance || "0") || 0;
      if (amount > accountBalance) {
        Swal.fire({ icon: "warning", title: "Insufficient Balance", text: "Selected account does not have enough balance." });
        return;
      }

      const formData = new FormData(payForm);

      fetch(APP_URL + "/finance/station-finances/action", {
        method: "POST",
        body: formData,
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (result) {
          if (result && result.success) {
            Swal.fire({
              icon: "success",
              title: "Payment Recorded",
              text: result.message || "Supplier payment processed successfully.",
              timer: 1800,
              showConfirmButton: false,
            }).then(function () {
              window.location.reload();
            });
            if (modal) modal.hide();
          } else {
            Swal.fire({
              icon: "error",
              title: "Payment Failed",
              text: (result && result.message) || "Unable to process payment.",
            });
          }
        })
        .catch(function () {
          Swal.fire({
            icon: "error",
            title: "Network Error",
            text: "Could not connect to server. Please try again.",
          });
        });
    });
  }
})();
