export default class EErrorCode {
    static get NO_ERROR() {
        return 0;
    }

    static get ERROR() {
        return 1;
    }

    static get VALIDATION_ERROR() {
        return 2;
    }

    static get UNAUTHORIZED() {
        return 401;
    }

    static get FORBIDDEN() {
        return 403;
    }

    static get NOT_FOUND() {
        return 404;
    }
}
