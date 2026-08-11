import BrowserLogger from "js/core/utils/BrowserLogger";
import AjaxHandler from "js/core/utils/AjaxHandler";
import ModalBase from "js/components/Modals/ModalBase";

export default class ExportImportManager extends ModalBase {
  constructor(options = {}) {
    super("ExportImportManager", {
      closeOnEsc: true,
      closeOnOverlayClick: true,
      preventBodyScroll: true,
      autoFocus: true,
      ...options.modalOptions
    });

    this.logger = new BrowserLogger("ExportImportManager");
    this.ajax = new AjaxHandler();

    this.options = {
      exportUrl: options.exportUrl || "/admin/footer/export",
      importUrl: options.importUrl || "/admin/footer/import",
      onExportSuccess: options.onExportSuccess || null,
      onImportSuccess: options.onImportSuccess || null,
      ...options
    };
  }

  async exportConfig() {
    this.logger.debug("Exporting configuration");

    try {
      const response = await this.ajax.get(this.options.exportUrl);

      if (response.success && response.data) {
        const blob = new Blob([JSON.stringify(response.data, null, 2)], {
          type: "application/json"
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `footer-config-${new Date().toISOString()}.json`;
        a.click();
        URL.revokeObjectURL(url);

        if (this.options.onExportSuccess) {
          this.options.onExportSuccess(response);
        }

        return { success: true };
      } else {
        throw new Error(response.error || "Export failed");
      }
    } catch (error) {
      this.logger.error("Export failed:", error);
      return { success: false, error: error.message };
    }
  }

  async importConfig(file) {
    if (!file) {
      return { success: false, error: "No file selected" };
    }

    const formData = new FormData();
    formData.append("config", file);

    try {
      const response = await this.ajax.request({
        url: this.options.importUrl,
        method: "POST",
        data: formData,
        json: true,
        timeout: 30000
      });

      if (response.success) {
        if (this.options.onImportSuccess) {
          this.options.onImportSuccess(response);
        }
        return { success: true, data: response };
      } else {
        throw new Error(response.error || "Import failed");
      }
    } catch (error) {
      this.logger.error("Import failed:", error);
      return { success: false, error: error.message };
    }
  }

  showImportModal() {
    const htmlContent = `
      <div class="modal-overlay" data-modal="import-config">
        <div class="modal-container modal-container--sm">
          <div class="modal-header">
            <h3>Import Footer Configuration</h3>
            <button class="modal-close" data-modal-close>&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Select JSON File</label>
              <input type="file" id="import-file-input" accept=".json" class="form-control">
              <small>File must be a valid footer configuration export</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-modal-cancel>Cancel</button>
            <button type="button" class="btn btn-primary" id="confirm-import-btn">Import</button>
          </div>
        </div>
      </div>
    `;

    this.showModal(htmlContent);

    const importBtn = this.currentModal?.querySelector("#confirm-import-btn");
    const fileInput = this.currentModal?.querySelector("#import-file-input");

    if (importBtn) {
      importBtn.addEventListener("click", async () => {
        if (!fileInput?.files[0]) {
          this.logger.warn("No file selected");
          return;
        }

        importBtn.disabled = true;
        importBtn.textContent = "Importing...";

        const result = await this.importConfig(fileInput.files[0]);

        if (result.success) {
          this.closeCurrentModal("import-success");
        } else {
          importBtn.disabled = false;
          importBtn.textContent = "Import";
          this.logger.error(result.error);
        }
      });
    }
  }
}
