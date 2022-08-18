const MobileWebMixin = {
    mounted() {
        let lastX;
        $(document).bind('touchmove', function (e){
            let newX = e.originalEvent.touches[0].clientX;
            if (lastX < newX) {
                $('.nav-ctn').addClass('nav-ctn--collapsed');
            }
            lastX = newX;
        });
    },
};
export default MobileWebMixin;
