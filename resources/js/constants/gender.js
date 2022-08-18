export default class EGender {
    static get MALE() {
        return 1;
    }
    static get FEMALE() {
        return 2;
    }
    static valueToName(val) {
    	switch (val) {
	        case this.MALE:
	            return 'Nam';
	        case this.FEMALE:
	            return 'Nữ';
	    }
        return null;
    }
}
