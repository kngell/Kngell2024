const formHandler = new FormHandler(formElement, {
  notificationConfig: {
    error: {
      permanent: true, // Make errors permanent
      duration: 10000 // 10 seconds if not permanent
    },
    success: {
      permanent: false,
      duration: 2000 // Quick 2-second success
    }
  },
  onSuccess: (result) => {
    console.log("Success!");
  },
  onError: (error) => {
    console.error("Error:", error);
  }
});

await formHandler.initialize();
