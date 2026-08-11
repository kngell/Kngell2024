const modalFlashChannel = new ModalFlashChannel(modalElement); // future class
this.formHandler = new FormHandler(form, { feedbackChannel: modalFlashChannel });

// Create notification channel
const notificationChannel = new NotificationChannel({
  position: "top-right",
  containerId: "app-notifications",
  error: { permanent: true, duration: 8000 },
  success: { permanent: false, duration: 3000 }
});

// Use directly
notificationChannel.success("Product saved successfully!");
notificationChannel.error("Failed to save product");

// Or use through FormHandler processors
const formHandler = new FormHandler(form, {
  feedbackChannel: notificationChannel,
  processors: {
    enabled: true,
    notification: {
      enabled: true,
      config: { permanentErrors: true }
    }
  }
});
