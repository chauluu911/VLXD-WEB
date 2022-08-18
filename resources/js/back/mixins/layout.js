import FChevronsLeftIcon from 'vue-feather-icons/icons/ChevronsLeftIcon'
import FChevronsRightIcon from 'vue-feather-icons/icons/ChevronsRightIcon'
import FUsersIcon from 'vue-feather-icons/icons/UsersIcon'
import FListIcon from 'vue-feather-icons/icons/ListIcon'
import FBoxIcon from 'vue-feather-icons/icons/BoxIcon'
import FTagIcon from 'vue-feather-icons/icons/TagIcon'
import FCreditCardIcon from 'vue-feather-icons/icons/CreditCardIcon'
import FMessageSquareIcon from 'vue-feather-icons/icons/MessageSquareIcon'
import FBellIcon from 'vue-feather-icons/icons/BellIcon'
import FSettingsIcon from 'vue-feather-icons/icons/SettingsIcon'
import {mapState} from "vuex";

const LayoutMixin = {
	components: {
		FChevronsLeftIcon,
		FChevronsRightIcon,
		FUsersIcon,
		FListIcon,
		FBoxIcon,
		FTagIcon,
		FCreditCardIcon,
		FMessageSquareIcon,
		FBellIcon,
		FSettingsIcon,
	},
	computed: {
		...mapState(['sidebarState']),
	},
	data: {
		isSidebarActive: true,
		isSidebarHovered: false,
	}
};
export default LayoutMixin;