class FirebaseUtil {
    constructor() {
    }

    checkFirebaseReady() {
        return new Promise((resolve) => {
            let firebaseReady = () => {
                if (window.firebase && window.firebase.apps && window.firebase.apps.length) {
                    console.log('fb readyyy');
                    resolve();
                } else {
                    console.log('timeout')
                    setTimeout(firebaseReady, 1000);
                }
            };
            firebaseReady();
        });
    }
}

const util = new FirebaseUtil();

export default util;
