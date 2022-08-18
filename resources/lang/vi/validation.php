<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':Attribute phải được chấp nhận.',
    'active_url' => ':Attribute không phải là một đường dẫn hợp lệ.',
    'after' => ':Attribute phải là một ngày sau :date.',
    'after_or_equal' => ':Attribute phải là một ngày sau hoặc bằng :date.',
    'alpha' => ':Attribute chỉ có thể chứa các chữ cái.',
    'alpha_dash' => ':Attribute chỉ có thể chứa các chữ cái, số, và dấu gạch dưới.',
    'alpha_num' => ':Attribute chỉ có thể chứa các chữ cái và số.',
    'array' => ':Attribute phải là một mảng.',
    'before' => ':Attribute phải là một ngày trước :date.',
    'before_or_equal' => ':Attribute phải là một ngày trước hoặc bằng :date.',
    'between' => [
        'numeric' => ':Attribute phải từ :min đến :max.',
        'file' => ':Attribute phải từ :min đến :max kilobytes.',
        'string' => ':Attribute phải từ :min đến :max kí tự.',
        'array' => ':Attribute phải từ :min đến :max phần tử.',
    ],
    'boolean' => ':Attribute phải là true hay false.',
    'confirmed' => ':attribute không khớp.',
    'date' => ':Attribute không phải là một ngày hợp lệ.',
    'date_format' => ':Attribute không đúng định dạng :format.',
    'different' => ':Attribute và :other phải khác nhau.',
    'digits' => ':Attribute phải có :digits chữ số.',
    'digits_between' => ':Attribute phải có từ :min đến :max chữ số.',
    'dimensions' => ':Attribute có kích thước ảnh không phù hợp.',
    'distinct' => ':Attribute có một giá trị bị trùng.',
    'email' => ':Attribute phải là một email hợp lệ.',
    'exists' => ':Attribute được chọn không hợp lệ.',
    'file'  => ':Attribute phải là một tệp.',
    'filled' => ':Attribute phải có một giá trị.',
    'image' => ':Attribute phải là hình ảnh.',
    'in' => ':Attribute được chọn không hợp lệ.',
    'in_array' => ':Attribute không có trong :other.',
    'integer' => ':Attribute phải là một số nguyên.',
    'ip' => ':Attribute phải là một địa chỉ IP hợp lệ.',
    'ipv4' => ':Attribute phải là một địa chỉ IPv4 hợp lệ.',
    'ipv6' => ':Attribute phải là một địa chỉ IPv6 hợp lệ.',
    'json' => ':Attribute phải là một chuỗi JSON hợp lệ.',
    'max' => [
        'numeric' => ':Attribute phải nhỏ hơn hoặc bằng :max.',
        'file' => ':Attribute phải nhỏ hơn :max kilobytes.',
        'string' => ':Attribute phải nhỏ hơn :max kí tự.',
        'array' => ':Attribute phải ít hơn :max phần tử.',
    ],
    'mimes' => ':Attribute phải là một tệp có dạng: :values.',
    'mimetypes' => ':Attribute phải là một tệp có dạng: :values.',
    'min' => [
        'numeric' => ':Attribute phải lớn hơn hoặc bằng :min.',
        'file' => ':Attribute phải lớn hơn :min kilobytes.',
        'string' => ':Attribute phải ít nhất :min kí tự.',
        'array' => ':Attribute phải nhiều hơn :min phần tử.',
    ],
    'not_in'  => ':attribute được chọn không hợp lệ.',
    'not_regex' => 'Định dạng :attribute không hợp lệ.',
    'numeric'  => ':Attribute phải là một số.',
    'present' => ':Attribute phải là hiện tại.',
    'phone' => 'Vui lòng nhập SĐT hợp lệ',
    'regex' => 'Định dạng :attribute không hợp lệ.',
    'required' => ':Attribute là bắt buộc.',
    'required_if' => ':Attribute là bắt buộc khi :other là :value.',
    'required_unless' => ':Attribute là bắt buộc trừ khi :other là :values.',
    'required_with' => ':Attribute là bắt buộc khi :values hiện diện.',
    'required_with_all' => ':Attribute là bắt buộc khi :values hiện diện.',
    'required_without' => ':Attribute là bắt buộc khi :values không hiện diện.',
    'required_without_all' => ':Attribute là bắt buộc khi không có :values hiện diện.',
    'same' => ':Attribute và :other phải trùng khớp.',
    'size' => [
        'numeric' => ':Attribute phải cỡ :size.',
        'file' => ':Attribute phải cỡ :size kilobytes.',
        'string' => ':Attribute phải cỡ :size kí tự.',
        'array' => ':Attribute phải chứa :size phần tử.',
    ],
    'string' => ':Attribute phải là một chuỗi.',
    'timezone' => ':Attribute phải là một khu vực hợp lệ.',
    'unique' => ':Attribute đã được sử dụng.',
    'uploaded' => ':Attribute tải lên thất bại.',
    'url' => 'Định dạng :Attribute không hợp lệ.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'password' => 'Mật khẩu chứa ít nhất 6 ký tự, có ít nhất 1 kí tự viết hoa',
        'name' => 'Tên không được chứa kí tự đặc biệt',
		'pick_required' => 'Vui lòng chọn một :attribute',
		'not_valid' => ':Attribute không hợp lệ',
        'affiliate_code' => 'Mã giới thiệu phải có 7 ký tự'
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
		'name' => 'Họ tên',
		'account' => 'Tài khoản',
		'phone' => 'Số điện thoại',
		'password' => 'Mật khẩu',
		'oldPassword' => 'Mật khẩu cũ',
		'newPassword' => 'Mật khẩu mới ',
		'password_confirmation' => 'Mật khẩu nhập lại',
		'gender' => 'Giới tính',
		'birthday' => 'Ngày sinh',
		'date_of_birth' => 'Ngày sinh',
		'email' => 'Email',
		'otp' => 'Mã xác nhận',
	],

];
