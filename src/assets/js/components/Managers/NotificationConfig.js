class SomeOtherForm extends BaseFormManager {
  constructor(options = {}) {
    super({
      // ... other options
      notificationConfig: {
        error: {
          permanent: false, // Errors auto-close
          duration: 5000 // After 5 seconds
        },
        success: {
          permanent: false,
          duration: 2000 // Quick success message
        }
      }
    });
  }
}
