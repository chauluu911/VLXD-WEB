export default {
    vi: {
        'branch' : "chi nhánh",
        common: {
            'see-more': 'Xem thêm',
            collapse: 'Thu gọn',
            loading: 'Đang tải...',
            error: {
                unknown: 'Có lỗi trong quá trình xử lý. Vui lòng thử lại sau hoặc liên hệ bộ phận hỗ trợ.',
                notAuthorization: 'Bạn không có quyền truy cập chức năng này. Liên hệ admin để được hỗ trợ.'
            },
            confirmation: {
                delete: 'Bạn có chắc chắn muốn xoá {objectName} này không?',
                update: 'Bạn có chắc chắn cập nhật thông tin {objectName} này không?',
                approve: 'Bạn có chắc chắn duyệt {objectName} này không?',
                resetPassword: 'Bạn có chắc chắn đặt lại mật khẩu mặc định cho {objectName} này không?',
                leavePage: 'Bạn có chắc muốn rời trang? Những thay đổi của bạn sẽ không được lưu',
            },
            pagination: {
                button: {
                    first: 'Trang đầu',
                    last: 'Trang cuối',
                },
                label: {
                    showPageSize: 'Hiển thị'
                }
            },
            button: {
                ok: 'Đồng ý',
                add: 'Thêm',
                add2: 'Thêm {objectName}',
                add3: 'Thêm mới',
                add4: 'Tạo {objectName}',
                delete: 'Xoá',
                delete2: 'Xoá {objectName}',
                edit: 'Sửa',
                edit2: 'Sửa {objectName}',
                save: 'Lưu',
                save2: 'Lưu {objectName}',
                update: 'Cập nhật',
                cancel: 'Huỷ',
                retry: 'Thử lại',
                search: 'Tìm kiếm',
                choose: 'Chọn',
                filter: 'Bộ lọc',
                download: 'Excel',
                back: 'Quay lại',
                approve: 'Duyệt',
                upload: 'Tải file lên',
                send: 'Gửi'
            },
            tooltip: {
                edit: 'Cập nhật',
                delete: 'Xóa',
                display_front: 'Hiển thị trước',
                display_behind: 'Hiển thị sau',
                approve: 'Duyệt',
                promote: 'Hiển thị ưu tiên',
                reset: 'Đặt lại mật khẩu {objectName}',
                non_editable: 'Không thể chỉnh sửa',
                reset_password: 'Đặt lại mật khẩu',
            },
            title: {
                edit: 'Chỉnh sửa {objectName}',
                create: 'Tạo {objectName}',
                config: 'Cấu hình {objectName}',
            },
        },
        // please add lang to resources/lang/vi/back/constant.php as well
        constant: {
            gender: {
                male: "Nam",
                female: "Nữ"
            },
            status: {
                status: 'Trạng thái',
                default: 'Mặc định',
                deleted: 'Đã xóa',
                pending: 'Chờ duyệt',
                waiting: 'Đang chờ',
                approved: 'Đã duyệt',
                confirm: 'Đã xác nhận',
                active: 'Hoạt động',
                sent: 'Đã gửi',
                reject: 'Từ chối',
            },
            payment_method: {
                payment_method: 'Hình thức thanh toán',
                coin: 'Xu',
                bank_transfer: 'Chuyển khoản',
                payment_gateway: 'Thanh toán trực tuyến',
            } ,
            order_status: {
                waiting: 'Mới',
                confirmed: 'Đã duyệt',
                admin_canceled: 'Hủy',
                user_canceled: 'Khách hàng hủy',
                completed: 'Hoàn thành',
            },
            delivery_status: {
                delivery_status: 'Vận chuyển',
                waiting_for_approval: 'Chờ xác nhận từ người bán',
                on_the_way: 'Đang giao',
                delivery_success: 'Giao thành công',
                customer_refuse: 'Khách hàng từ chối',
            },
            category: {
                class_1: 'Danh mục',
                class_2: 'Danh mục con',
                class_3: 'Danh mục con 2',
                ads: 'Danh mục quảng cáo',
            },
            payment_status: {
                paid: 'Đã thanh toán',
                unpaid: 'Chưa thanh toán',
            },
            display_status: {
                hidden: 'Ẩn',
                showing: 'Hiển thị',
            },
            sell_status: {
                selling: 'Đang bán',
                sold: 'Đã bán',
            },
            target_type: {
                all: 'Tất cả',
                specific: 'Chỉ định',
            },
        },
        table: {
            column: {
                no: 'STT',
                code: 'Mã',
                phone: 'SĐT',
                address: 'Địa chỉ',
                created_at: 'Ngày tạo',
                action: 'Hành động',
                custom: 'Tùy chỉnh',
                status: 'Trạng thái',
                image: 'Hình ảnh',
                updated_at: 'Cập nhật lần cuối',
                // business specific
                branch: 'Chi nhánh',
            },
            empty: 'Không có dữ liệu'
        },
        'attach-file': {
            guide: {
                click: 'Bấm vào đây để chọn hình',
                dragndrop: 'hoặc kéo thả hình vào đây',
                'change-image': 'Đổi hình khác'
            }
        },
        label: {
            phone: 'SĐT',
            address: 'Địa chỉ',
            'date-of-birth': 'Ngày sinh',
            email: 'Email',
            role: 'Vai trò',
            note: 'Ghi chú',
            status: 'Trạng thái',
            payment_status: 'Thanh toán',
            delivery_status: 'Vận chuyển',
            create_at: 'Ngày tạo',
        },
        placeholder: {
            phone: 'Nhập số điện thoại',
            address: 'Nhập địa chỉ',
            'date-of-birth': 'dd/mm/yyyy',
            email: 'Nhập địa chỉ email',
            note: 'Nội dung',
            filter: {
                role: 'Chọn vai trò',
                branch: 'Chọn chi nhánh',
                created_at_from: 'Từ ngày',
                created_at_to: 'Đến ngày',
                status: 'Trạng thái',
                payment_type: 'Loại thanh toán',
            }
        },
        filter: {
            empty: 'Không có dữ liệu',
            type_to_search: 'Tìm kiếm',
            other: 'Khác',
        }
    },
    en: {
        common: {
            'branch' : "Branch",
            'see-more': 'See more',
            collapse: 'Collapse',
            loading: 'Loading...',
            error: {
                unknown: 'Có lỗi trong quá trình xử lý. Vui lòng thử lại sau hoặc liên hệ bộ phận hỗ trợ.',
                notAuthorization: 'Bạn không có quyền truy cập chức năng này. Liên hệ admin để được hỗ trợ.'
            },
            confirmation: {
                delete: 'Do you want to delete this {objectName}?',
                update: 'Bạn có chắc chắn cập nhật thông tin {objectName} này không?',
                approve: 'Do you want to approve this {objectName}?',
                approveMulti: 'Do you want to approve this {objectName}? | Do you want to approve these {objectName}',
                resetPassword: 'Do you want to reset password for this {objectName}?',
                leavePage: 'Bạn có chắc muốn rời trang? Những thay đổi của bạn sẽ không được lưu',
            },
            pagination: {
                button: {
                    first: 'Trang đầu',
                    last: 'Trang cuối',
                },
                label: {
                    showPageSize: 'Hiển thị'
                }
            },
            button: {
                ok: 'Ok',
                add: 'Add',
                add2: 'Add {objectName}',
                add3: 'Add new',
                add4: 'Create {objectName}',
                delete: 'Delete',
                delete2: 'Delete {objectName}',
                edit: 'Edit',
                edit2: 'Edit {objectName}',
                save: 'Save',
                save2: 'Save {objectName}',
                update: 'Update',
                cancel: 'Cancel',
                retry: 'Retry',
                search: 'Search',
                choose: 'Choose',
                filter: 'Filter',
                download: 'Excel',
                back: 'Back',
                approve: 'Approve',
                upload: 'Upload file',
            },
            tooltip: {
                edit: 'Edit',
                delete: 'Delete',
                approve: 'Approve',
                promote: 'Promote',
                reset: 'Reset {objectName}',
                non_editable: 'Không thể chỉnh sửa',
                reset_password: 'Reset password',
                reject: 'Từ chối',
            },
            title: {
                edit: 'Chỉnh sửa {objectName}',
                create: 'Tạo {objectName}',
                detail: 'Chi tiết {objectName}',
            },
        },
        // please add lang to resources/lang/vi/back/constant.php as well
        constant: {
            gender: {
                male: "Nam",
                female: "Nữ"
            },
            status: {
                status: 'Trạng thái',
                default: 'Mặc định',
                deleted: 'Đã xóa',
                except_deleted: '',
                pending: 'Chờ duyệt',
                approved: 'Đã duyệt',
                waiting: 'Đang chờ',
                active: 'Active',
                sent: 'Đã gửi',
            },
            order_status: {
                waiting: 'Mới',
                confirmed: 'Đã duyệt',
                admin_canceled: 'Hủy',
                user_canceled: 'Khách hàng hủy',
                completed: 'Hoàn thành',
            },
            category: {
                class_1: 'Danh mục',
                class_2: 'Danh mục con',
                class_3: 'Danh mục con 2',
                ads: 'Danh mục quảng cáo',
            },
            payment_status: {
                paid: 'Đã thanh toán',
                unpaid: 'Chưa thanh toán',
            },
            display_status: {
                hidden: 'Ẩn',
                showing: 'Hiển thị',
            },
            sell_status: {
                selling: 'Đang bán',
                sold: 'Đã bán',
            },
            target_type: {
                all: 'Tất cả',
                specific: 'Chỉ định',
            },
        },
        table: {
            column: {
                no: 'STT',
                code: 'Mã',
                phone: 'SĐT',
                address: 'Địa chỉ',
                created_at: 'Ngày tạo',
                action: 'Hành động',
                custom: 'Tùy chỉnh',
                status: 'Trạng thái',
                image: 'Hình ảnh',
                updated_at: 'Cập nhật lần cuối',
                // business specific
                branch: 'Chi nhánh',
            },
            empty: 'Không có dữ liệu'
        },
        'attach-file': {
            guide: {
                click: 'Bấm vào đây để chọn hình',
                dragndrop: 'hoặc kéo thả hình vào đây',
                'change-image': 'Đổi hình khác'
            }
        },
        label: {
            phone: 'SĐT',
            address: 'Địa chỉ',
            'date-of-birth': 'Ngày sinh',
            email: 'Email',
            role: 'Vai trò',
            note: 'Ghi chú',
            status: 'Trạng thái',
            payment_status: 'Thanh toán',
            create_at: 'Ngày tạo',
        },
        placeholder: {
            phone: 'Nhập số điện thoại',
            address: 'Nhập địa chỉ',
            'date-of-birth': 'dd/mm/yyyy',
            email: 'Nhập địa chỉ email',
            note: 'Nội dung',
            filter: {
                role: 'Chọn vai trò',
                branch: 'Chọn chi nhánh',
                created_at_from: 'Từ ngày',
                created_at_to: 'Đến ngày',
                status: 'Trạng thái',
            }
        },
        filter: {
            empty: 'Không có dữ liệu',
            type_to_search: 'Tìm kiếm',
            other: 'Khác',
        }
    },
    fr: {
        common: {
            'see-more': 'See more',
            collapse: 'Collapse',
            loading: 'Đang tải...',
            error: {
                unknown: 'Có lỗi trong quá trình xử lý. Vui lòng thử lại sau hoặc liên hệ bộ phận hỗ trợ.',
                notAuthorization: 'Bạn không có quyền truy cập chức năng này. Liên hệ admin để được hỗ trợ.'
            },
            confirmation: {
                delete: 'Bạn có chắc chắn muốn xoá {objectName} này không?',
                update: 'Bạn có chắc chắn cập nhật thông tin {objectName} này không?',
                approve: 'Bạn có chắc chắn duyệt {objectName} này không?',
                approveMulti: 'Bạn có chắc chắn duyệt {objectName} này không?',
                resetPassword: 'Do you want to reset password for this {objectName}?',
                leavePage: 'Bạn có chắc muốn rời trang? Những thay đổi của bạn sẽ không được lưu',
            },
            pagination: {
                button: {
                    first: 'Trang đầu',
                    last: 'Trang cuối',
                },
                label: {
                    showPageSize: 'Hiển thị'
                }
            },
            button: {
                ok: 'Đồng ý',
                add: 'Thêm',
                add2: 'Thêm {objectName}',
                add3: 'Thêm mới',
                add4: 'Tạo {objectName}',
                delete: 'Xoá',
                delete2: 'Xoá {objectName}',
                edit: 'Sửa',
                edit2: 'Sửa {objectName}',
                save: 'Lưu',
                save2: 'Lưu {objectName}',
                update: 'Cập nhật',
                cancel: 'Huỷ',
                retry: 'Thử lại',
                search: 'Tìm kiếm',
                choose: 'Chọn',
                filter: 'Bộ lọc',
                download: 'Excel',
                back: 'Quay lại',
                approve: 'Approve',
            },
            tooltip: {
                edit: 'Cập nhật',
                delete: 'Xóa',
                approve: 'Duyệt',
                promote: 'Hiển thị ưu tiên',
                reset: 'Đặt lại mật khẩu {objectName}',
                non_editable: 'Không thể chỉnh sửa',
                reset_password: 'Đặt lại mật khẩu',
            },
            title: {
                edit: 'Chỉnh sửa {objectName}',
                create: 'Tạo {objectName}',
                detail: 'Chi tiết {objectName}',
            },
        },
        // please add lang to resources/lang/vi/back/constant.php as well
        constant: {
            gender: {
                male: "Nam",
                female: "Nữ"
            },
            status: {
                status: 'Trạng thái',
                default: 'Mặc định',
                deleted: 'Đã xóa',
                pending: 'Chờ duyệt',
                approved: 'Đã duyệt',
                waiting: 'Đang chờ',
                active: 'Active',
                sent: 'Đã gửi',
            },
            order_status: {
                waiting: 'Mới',
                confirmed: 'Đã duyệt',
                admin_canceled: 'Hủy',
                user_canceled: 'Khách hàng hủy',
                completed: 'Hoàn thành',
            },
            category: {
                class_1: 'Danh mục',
                class_2: 'Danh mục con',
                class_3: 'Danh mục con 2',
                ads: 'Danh mục quảng cáo',
            },
            payment_status: {
                paid: 'Đã thanh toán',
                unpaid: 'Chưa thanh toán',
            },
            display_status: {
                hidden: 'Ẩn',
                showing: 'Hiển thị',
                detail: 'Chi tiết {objectName}',
            },
            sell_status: {
                selling: 'Đang bán',
                sold: 'Đã bán',
            },
            target_type: {
                all: 'Tất cả',
                specific: 'Chỉ định',
            },
        },
        table: {
            column: {
                no: 'STT',
                code: 'Mã',
                phone: 'SĐT',
                address: 'Địa chỉ',
                created_at: 'Ngày tạo',
                action: 'Hành động',
                custom: 'Tùy chỉnh',
                status: 'Trạng thái',
                image: 'Hình ảnh',
                updated_at: 'Cập nhật lần cuối',
                // business specific
                branch: 'Chi nhánh',
            },
            empty: 'Không có dữ liệu'
        },
        'attach-file': {
            guide: {
                click: 'Bấm vào đây để chọn hình',
                dragndrop: 'hoặc kéo thả hình vào đây',
                'change-image': 'Đổi hình khác'
            }
        },
        label: {
            phone: 'SĐT',
            address: 'Địa chỉ',
            'date-of-birth': 'Ngày sinh',
            email: 'Email',
            role: 'Vai trò',
            note: 'Ghi chú',
            status: 'Trạng thái',
            payment_status: 'Thanh toán',
            create_at: 'Ngày tạo',
        },
        placeholder: {
            phone: 'Nhập số điện thoại',
            address: 'Nhập địa chỉ',
            'date-of-birth': 'dd/mm/yyyy',
            email: 'Nhập địa chỉ email',
            note: 'Nội dung',
            filter: {
                role: 'Chọn vai trò',
                branch: 'Chọn chi nhánh',
                created_at_from: 'Từ ngày',
                created_at_to: 'Đến ngày',
                status: 'Trạng thái',
            }
        },
        filter: {
            empty: 'Không có dữ liệu',
            type_to_search: 'Tìm kiếm',
            other: 'Khác',
        }
    }
}
