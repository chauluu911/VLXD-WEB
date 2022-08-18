<template>
    <div id="chat-container">
        <div class="chat-wrapper-horizontal">
            <div class="row m-0 chat-wrapper-vertical">
                <div class="col-md-16 col-48 p-0 left-block-wrapper" :class="{active: currentTab === 'conversations'}">
                    <div class="left-block">
                        <div class="left-block__filter">
                            <input
                                id="user-search-input"
                                v-model="filters.q"
                                type="text"
                                class="form-control"
                                placeholder="Tìm kiếm"
                            >
                            <label for="user-search-input">
                                <i class="fas fa-search"></i>
                            </label>
                        </div>
                        <div ref="conversationListEl" class="conversation-list" >
                            <div v-if="!initialized || Object.keys(this.loading.targetInfoList).length !== 0"
                                 class="conversation-list__loading"
                            >
                                <div>
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    {{ $t('common.loading') }}
                                </div>
                            </div>
                            <div v-else-if="!conversations.length && !userId" class="conversation-list__empty">
                                <div>
                                    {{ $t('table.empty') }}
                                </div>
                            </div>
                            <template v-else>
                                <template v-for="(item, index) in orderedConversations">
                                    <div
                                        :id="item.conversation_id ? item.conversation_id : Number.MAX_SAFE_INTEGER"
                                        :key="item.conversation_id ? `${item.conversation_id} ${index}`:`${Number.MAX_SAFE_INTEGER} ${index}` "
                                        :class="{
											'active' : currentConversation.conversation_id === item.conversation_id || item === currentConversation,
											'is-seen': item.conversation_id && !item.number_of_unseen_messages,
										}"
                                        class="conversation mr-1"
                                        @click="$_setCurrentConversation(item), currentTab = 'messages'"
                                    >
                                        <div v-if="!item.target.avatar_type || item.target.avatar_type != EAvatarType.VIDEO"
                                            class="chat-user-avatar"
                                            :style="{'background-image': `url(${StringUtil.getAvatarPath(item.target.avatar_path)})`}"
                                        >
                                            <div
                                                v-if="item.number_of_unseen_messages"
                                                class="conversation__number-unseen"
                                                :class="{
													'conversation__number-unseen--plus': item.number_of_unseen_messages > 9
												}"
                                            >
												<span>
													{{ item.number_of_unseen_messages > 9 ? '9+' : item.number_of_unseen_messages }}
												</span>
                                            </div>
                                        </div>
                                        <div v-else
                                             class="chat-user-avatar"
                                        >
                                             <span
                                                 class="rounded-circle"
                                             >
                                                <span class="shop-avatar">
                                                    <video autoplay muted loop
                                                           :src="StringUtil.getAvatarPath(item.target.avatar_path)"
                                                           style="width: 125px"></video>
                                                </span>
                                            </span>
                                            <div
                                                v-if="item.number_of_unseen_messages"
                                                class="conversation__number-unseen"
                                                :class="{
													'conversation__number-unseen--plus': item.number_of_unseen_messages > 9
												}"
                                            >
												<span>
													{{ item.number_of_unseen_messages > 9 ? '9+' : item.number_of_unseen_messages }}
												</span>
                                            </div>
                                        </div>
                                        <div class="clearfix">
											<span class="conversation__username">
												{{ item.target.name }}
											</span>
                                            <span class="conversation__updated-at" v-html="$_showTimeOrDate(item.last_updated_at, item.lastUpdatedAtTimeStr, item.lastUpdatedAtDateStr)"></span>
                                        </div>
                                        <div
                                            class="conversation__last-message"
                                            v-shave="{height: 27}"
                                        >
                                            {{  item.last_messages }}
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="col-md-32 col-48 p-0 right-block-wrapper" :class="{active: currentTab === 'messages'}">
                    <div class="right-block">
                        <div class="right-block__title">
                            <div class="d-flex align-items-center justify-content-start">
                                <div class="icon-back-to-list-conversation pr-2" @click="currentTab = 'conversations'">
                                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                                </div>
                                <div
                                    v-if="currentConversation.target &&
                                    (!currentConversation.target.avatar_type || currentConversation.target.avatar_type != EAvatarType.VIDEO)"
                                    class="chat-user-avatar"
                                    :style="{'background-image': `url(${StringUtil.getAvatarPath(currentConversation.target.avatar_path)})`}">
                                </div>
                                <div
                                    v-if="currentConversation.target &&
                                    currentConversation.target.avatar_type == EAvatarType.VIDEO"
                                    class="chat-user-avatar"
                                    >
                                    <span
                                        class="rounded-circle"
                                    >
                                                <span class="shop-avatar">
                                                    <video autoplay muted loop
                                                           :src="StringUtil.getAvatarPath(currentConversation.target.avatar_path)"
                                                           style="width: 125px"></video>
                                                </span>
                                            </span>
                                </div>
                                <span v-if="currentConversation.target" class="right-block__title__username">
								{{ currentConversation.target.name }}
							</span>
                            </div>
                            <div class="">
                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                            </div>
                        </div>
                        <!--                        <div-->
                        <!--                            v-if="user && user.customer_type === ECustomerType.SELLER && currentConversation && currentConversation.customer"-->
                        <!--                            class="col right-block__profile-sheet">-->
                        <!--                            <div class="clearfix right-block__profile-sheet__content">-->
                        <!--                                <div class="right-block__profile-sheet__description">-->
                        <!--                                    <img src="/images/icon/open-document.svg">-->
                        <!--                                    <span>-->
                        <!--										{{ $t('profile_sheet_label', {name: currentConversation.name}) }}-->
                        <!--									</span>-->
                        <!--                                </div>-->
                        <!--                                <div class="right-block__profile-sheet__action">-->
                        <!--                                    <a href="javascript:void(0)" @click="$_showUserInfo(currentConversation.customer)">-->
                        <!--                                        {{ $t('button.view_more') }}-->
                        <!--                                    </a>-->
                        <!--                                </div>-->
                        <!--                            </div>-->
                        <!--                        </div>-->
                        <div class="message-list"  ref="conversationMessageListEl">
                            <div v-if="Object.keys(this.loading.targetInfoList).length == 0" class="h-100">
                                <div
                                    v-if="loading.currentConversationMessage "
                                    class="message-list__loading-more"
                                >
                                    <div>
                                        <div class="spinner-border" role="status">
                                            <span class="sr-only">Loading... </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="message-list__content">
                                    <div
                                        v-if="loading.currentConversation"
                                        class="message-list__loading"
                                    >
                                        <div>
                                            <div class="spinner-border" role="status">
                                                <span class="sr-only">Loading... </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else id="xxxxx">
                                        <div
                                            v-for="(group, gIndex) in messageGroups"
                                            class="message-block"
                                            :class="{
										'message-block--current-user': group.from_user_id === user.id,
									}"
                                        >
                                            <!--<div v-if="!index || item.timestamp - messages[index - 1].timestamp > 3600000"
                                                 class="messenger__message__send_at col-48" v-html="item.createdAtStr">
                                            </div>-->
                                            <div v-if="group.from_user_id !== user.id" class="message-block__user">
                                                <div>
                                                    <div
                                                        class="chat-user-avatar"
                                                        :style="{'background-image': `url(${StringUtil.getAvatarPath(currentConversation.target.avatar_path)})`}"
                                                    >
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="message-block__message-list">
                                                <div
                                                    v-for="item in group.messages"
                                                    class="message-list__message"
                                                    :class="{
												'message-list__message--text': item.message_type === EConversationMessageType.TEXT,
											}"
                                                >
                                                    <template v-if="item.message_type === EConversationMessageType.TEXT">
                                                        <span v-html="StringUtil.nl2br(item.message)"></span>
                                                    </template>
                                                    <template v-else-if="item.message_type === EConversationMessageType.IMAGE">
                                                        <template v-if="item.uploaded">
                                                            <div v-for="imgSrc in item.message" @click="$_reviewImage(`${resourceUrlPath}/${imgSrc}`)"
                                                                 class="message-list__message--image"
                                                                 :class="{
															'message-list__message--image--one': item.message.length === 1,
															'message-list__message--image--multi': item.message.length > 1,
														 }"
                                                            >
                                                                <div class="background-image-center" :style="{'background-image': `url(${resourceUrlPath}/${imgSrc})`}"></div>
                                                            </div>
                                                        </template>
                                                        <template v-else>
                                                            <div v-for="img in item.imageList" @click="$_reviewImage(img.src)"
                                                                 class="message-list__message--image"
                                                                 :class="{
															'message-list__message--image--one': item.imageList.length === 1,
															'message-list__message--image--multi': item.imageList.length > 1,
														 }"
                                                                 :style="{
															'opacity': item.loading ? 0.7 : 1,
														 }"
                                                            >
                                                                <div class="background-image-center" :style="{'background-image': `url(${img.src})`}"></div>
                                                            </div>
                                                            <div v-if="item.loading" class="progress">
                                                                <div
                                                                    class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                                                    role="progressbar"
                                                                    :style="{width: `${item.uploadedPercent * 100}%`, height: '10px'}"
                                                                ></div>
                                                            </div>
                                                            <div v-if="item.error" class="text-danger">
                                                                <span>{{ $t('error.upload_image') }}</span>
                                                                <a href="javascript:void(0)" @click="$_sendMessage(item)" :disabled="item.loading">{{ $t('common.button.retry') }}</a>
                                                            </div>
                                                        </template>
                                                    </template>
                                                    <template v-else-if="item.message_type === EConversationMessageType.FILE">
                                                        <template v-if="item.uploaded">
                                                            <div
                                                                 class="message-list__message--file"
                                                            >
                                                                <div class="message-list__message--file--name">
                                                                    {{item.message[1]}}
                                                                </div>
                                                                <div class="message-list__message--file--info">
                                                                    <div style="opacity: 0.5;">
                                                                        {{item.message[3]}}
                                                                    </div>
                                                                    <div>
                                                                        <a
                                                                            :href="`${resourceUrlPath}/${item.message[0]}`"
                                                                           target="_blank" download="" >
                                                                            <i style="color: #000000DD"
                                                                                class="fa fa-download" aria-hidden="true">
                                                                            </i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template v-else>
                                                            <div
                                                                class="message-list__message--file"
                                                                :style="{'opacity': item.loading ? 0.7 : 1}"
                                                            >
                                                                <div class="message-list__message--file--name">
                                                                    {{item.file.name}}
                                                                </div>
                                                                <div class="message-list__message--file--info">
                                                                    <div style="opacity: 0.5;">
                                                                        {{item.file.size}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div v-if="item.loading" class="progress">
                                                                <div
                                                                    class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                                                    role="progressbar"
                                                                    :style="{width: `${item.uploadedPercent * 100}%`, height: '10px'}"
                                                                ></div>
                                                            </div>
                                                            <div v-if="item.error" class="text-danger">
                                                                <span>{{ $t('error.upload_file') }}</span>
                                                                <a href="javascript:void(0)" @click="$_sendMessage(item)" :disabled="item.loading">{{ $t('common.button.retry') }}</a>
                                                            </div>
                                                        </template>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="message-selected-image-block" v-if=" !!chatInput.file || (chatInput.images && chatInput.images.length)">
                            <div v-if="!!chatInput.file"
                                 :style="{top: chatInput.images.length ? '-20px' : '0px'}"
                                 style="background: #f76301; padding: 0px 10px">
                                <div>
                                    <i class="fa fa-file" aria-hidden="true"></i>
                                </div>
                                <div style="word-break: break-all; text-overflow: ellipsis; overflow: hidden">
                                    {{chatInput.file.name}}
                                </div>
                                <div>
                                    {{chatInput.file.size}}
                                </div>
                                <a
                                    href="javascript:void(0)"
                                    @click="chatInput.file = null"
                                >
                                    <img src="/images/icon/cancel-24px.svg" width="20px;">
                                </a>
                            </div>
                            <div
                                v-for="(image, index) in chatInput.images"
                                class="background-image-center"
                                :style="{'background-image': `url(${image.src})`}"
                            >
                                <a
                                    href="javascript:void(0)"
                                    @click="chatInput.images.splice(index, 1)"
                                >
                                    <img src="/images/icon/cancel-24px.svg" width="20px;">
                                </a>
                                <div
                                    v-if="image.loading"
                                    class="spinner-border"
                                    role="status"
                                    style="position: absolute; top: 50%; left: 50%;"
                                >
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="message-input-block"
                        >
                            <input
                                ref="chatTextInputEl"
                                v-model="chatInput.text"
                                type="text"
                                class="message-input"
                                :placeholder="$t('placeholder.type_message')"
                                :disabled="chatDisabled"
                                @keypress.enter="$_sendMessage()"
                            >
                            <div class="message-input__action">
                                <input
                                    ref="chatFileInputEl"
                                    type="file"
                                    accept=".jpg, .jpeg, .png, .mp3, .pdf, .txt, .xlsx, .xls, .zip"
                                    style="display: none;"
                                    @change="$_handleUploadFile"
                                >
                                <a
                                    class="message-input__action__a-btn"
                                    :disabled="chatDisabled"
                                    @click="chatDisabled || $refs.chatFileInputEl.click()"
                                >
                                    <i class="fa fa-paperclip" style=""></i>
                                </a>
                                <input
                                    ref="chatImageInputEl"
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    style="display: none;"
                                    @change="$_handleUploadImageFile"
                                >
                                <a
                                    class="message-input__action__a-btn"
                                    :disabled="chatDisabled"
                                    @click="chatDisabled || $refs.chatImageInputEl.click()"
                                >
                                    <i class="fa fa-camera" style=""></i>
                                </a>
                                <button
                                    class="btn message-input__action__send-btn"
                                    :disabled="chatDisabled"
                                    @click="$_sendMessage()"
                                >
                                    <!--                                    {{ $t('common.button.send') }}-->
                                    <div
                                        v-if="loading.chatInput || loading.chatInputImage"
                                        class="spinner-border text-danger"
                                        role="status"
                                        style="width: 20px; height: 20px; margin-left: 10px;"
                                    >
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <i v-else class="far fa-paper-plane" style=""></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div ref="reviewUserModalEl" class="modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body" v-if="reviewUser">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="form-group">
                            <label>{{ $t('attributes.name') }}</label>
                            <input v-model="reviewUser.username" class="form-control bg-white" disabled>
                        </div>
                        <div class="form-group">
                            <label>ID</label>
                            <input v-model="reviewUser.code" class="form-control bg-white" disabled>
                        </div>
                        <template v-if="reviewUser.attach_detail_info">
                            <!--<div class="form-group">
                                <label>Email</label>
                                <input v-model="reviewUser.email" class="form-control bg-white" disabled>
                            </div>-->
                            <div class="form-group">
                                <label>{{ $t('attributes.career') }}</label>
                                <input v-model="reviewUser.career" class="form-control bg-white" disabled>
                            </div>
                            <div class="form-group">
                                <label>{{ $t('attributes.position') }}</label>
                                <input v-model="reviewUser.position" class="form-control bg-white" disabled>
                            </div>
                            <div class="form-group">
                                <label>{{ $t('attributes.years_of_experience') }}</label>
                                <input v-model="reviewUser.years_of_experience" class="form-control bg-white" disabled>
                            </div>
                            <div class="form-group">
                                <label>{{ $t('attributes.experience') }}</label>
                                <textarea v-model="reviewUser.experience" class="form-control bg-white" disabled></textarea>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div ref="reviewImageModalEl" class="modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <img class="w-100" :src="reviewImageUrl">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ErrorCode from "../../constants/error-code";
import EConversationMessageType from "../../constants/conversation-message-type";
import customerMessageManagementMessages from '../../locales/front/customer-message-management';
import EUserType from "../../constants/user-type";
import ECustomerType from "../../constants/customer-type";
import EErrorCode from "../../constants/error-code";
import EAvatarType from "../../constants/avatar-type";
import EConnectStatus from "../../constants/connect-status";
import FChevronLeftIcon from "vue-feather-icons/icons/ChevronLeftIcon";
import FXIcon from "vue-feather-icons/icons/XIcon";

const firebaseRef = {
    userConversationList(userId) {
        return `chats_by_user/u${userId}/_conversation`;
    },
    userConversation(userId, conversationUid) {
        return `chats_by_user/u${userId}/_conversation/${conversationUid}`;
    },
    userConversationDeleted(userId, conversationUid) {
        return `chats_by_user/u${userId}/_conversation/${conversationUid}/deleted`;
    },
    conversationMemberList(conversationUid) {
        return `members/${conversationUid}`;
    },
    conversationMember(conversationUid, userId) {
        return `members/${conversationUid}/u${userId}`;
    },
    memberNumberOfUnseenMessage(conversationUid, userId) {
        return `members/${conversationUid}/u${userId}/number_of_unseen_messages`;
    },
    userNumberOfUnseenMessage(userId) {
        return `chats_by_user/u${userId}/number_of_unseen_messages`;
    },
    messageByConversation(conversationUid) {
        return `messages_by_conversation/${conversationUid}`;
    }
};

let database;
let oldSnapShotJson = {};
export default {
    name: "CustomerMessageManagement",
    i18n: {
        messages: customerMessageManagementMessages
    },
    components: {
        FChevronLeftIcon,
        FXIcon,
    },
    props: ['userId','firstMessage'],
    data() {
        return {
            user: null,
            resourceUrlPath: null,

            filters: {
                q: null,
            },
            timeout: {
                q: null,
            },
            error: {
                all: null,
                conversation: null,
                chat: null,
            },
            loading: {
                conversation: false,
                chat: false,
                chatInput: false,
                currentConversation: false,
                currentConversationMessage: false,
                staffFilter: false,
                userInfoList: {},
                targetInfoList: {},
                loadMoreUserInfoList: false,
                firstLoadConversationFromServer: false,
            },
            deleting: {},

            conversations: [],
            userInfoList: {}, // loaded user info
            shopInfoList: {}, //load shop info
            targetInfoList: {}, //load target info
            currentConversation: {},
            messages: [], // list messages
            messagesPage: 1,

            userInfoFilter: null,

            chatInput: {
                images: [],
                file:  null,
                text: '',
            },
            reviewImageUrl: null,
            reviewUser: null,

            initialized: false,
            firebase: {
                refs: [],
            },

            // to check should show Time or date
            today: this.$_formatDate(Date.now()),
            todayTimestamp: Date.now(),

            currentTab: 'conversations',
            EConversationMessageType,
            EUserType,
            EAvatarType,
            ECustomerType,
            EConnectStatus,
            Util: common,
            StringUtil: stringUtil,
            DateUtil: dateUtil,
            FirebaseUtil: firebaseUtil,
            FileUtil: fileUtil,
        };
    },
    computed: {
        routes() {
            let currentConversationId = this.$_getConversationIdVal(this.currentConversation.conversation_id);
            let baseUrl = '/messenger'
            return {
                config: `${baseUrl}/config`,
                userInfo: `${baseUrl}/user`,
                shopInfo: `${baseUrl}/shop/info`,
                conversationList: `${baseUrl}/conversation`,
                info: `${baseUrl}/conversation/info`,
                saveImage: `${baseUrl}/conversation/image`,
                saveFile: `${baseUrl}/conversation/file`,
                permission: `${baseUrl}/conversation/${currentConversationId}/permission`,
                assignStaff: `${baseUrl}/conversation/${currentConversationId}/assign-staff`,
                removeStaff: `${baseUrl}/conversation/${currentConversationId}/remove-staff`,
                updateInterestStatus: `${baseUrl}/interest/${this.currentConversation.interest_id}/approve`,
                conversationExists: `${baseUrl}/conversation/exists`,
                create: `${baseUrl}/conversation/create`,
                chat_notification: (conversationId) => `${baseUrl}/conversation/${conversationId}/chat-notify`,
            }
        },
        orderedConversations() {
            return this.conversations
                .filter((item) => {
                    return !this.filters.q ||
                        item.target.name &&
                        item.target.name.toLowerCase().includes(this.filters.q.toLowerCase());
                })
                .sort((a, b) => {
                    return a.orderKey < b.orderKey ? 1 : -1;
                });
        },
        orderedMessages() {
            return this.messages.sort((a, b) => {
                return a.timestamp - b.timestamp;
            })
        },
        messageGroups() {
            let result = [],
                currentBlock = null;
            this.orderedMessages.forEach((message, index) => {
                if (!currentBlock || currentBlock.from_user_id !== message.from_user_id) {
                    currentBlock = {
                        ...message,
                        messages: [],
                    };
                } else {
                    currentBlock = {
                        ...currentBlock,
                        ...message,
                    }
                }
                currentBlock.messages.push(message);

                if (index + 1 >= this.orderedMessages.length
                    || this.orderedMessages[index + 1].from_user_id !== currentBlock.from_user_id
                    || this.orderedMessages[index + 1].createdAtDateStr !== currentBlock.createdAtDateStr) {
                    result.push({
                        ...currentBlock,
                    });
                    currentBlock = null;
                }
            });
            return result;
        },
        "loading.chatInputImage"() {
            return this.chatInput.images.length
                && !this.chatInput.images.every((item) => !!item.src);
        },
        chatDisabled() {
            return (!this.currentConversation.conversation_id && this.currentConversation.interest_id)
                || this.loading.chatInput
                || this.loading.chatInputImage;
        }
    },
    watch: {
        "filters.q"(val) {
            console.log('g1');
            if (val) {
                console.log('g2');
                clearTimeout(this.timeout.q);
                this.timeout.q = setTimeout(() => {
                    console.log('g3');
                    this.$_getConversationInfoFromServer();
                }, 1000);
            }
        },
        /*async userInfoFilter(val) {
            if (!val) {
                return;
            }
            let conversationId = await this.$_checkCustomerConversationExists(val.id);
            if (!conversationId) {
                let conversationInfo = {
                    conversation_id: null,
                    conversation_uid: null,
                    last_messages: null,
                    lastUpdatedAtStr: null,
                    orderKey: `1_${Date.now()}`,
                    customer: {
                        id: val.id,
                        uid: `u${val.id}`,
                        name: val.name,
                        phone: val.phone,
                        avatar_path: val.avatar_path,
                    },
                    staff: null,
                    number_of_unseen_messages: 0,
                };
                this.conversations.unshift(conversationInfo);
                this.$_setCurrentConversation(conversationInfo);
                this.currentTab = 'messages';
                return;
            }
            let conversation = this.conversations.find((conversation) => {
                return conversation.conversation_id === conversationId;
            });
            if (!conversation) {
                conversation = await this.$_getConversationInfoFromFirebase(`c${conversationId}`);
            }
            if (conversation) {
                this.$_setCurrentConversation(conversation);
            }
            this.currentTab = 'messages';
        }*/
    },
    created() {
        window.history.pushState(null, null,'/messenger');
        this.init();
    },
    beforeDestroy() {
        this.currentConversation.conversation_id = null;

        // off all firebase ref
        this.firebase.refs.forEach((ref) => ref.off());

        // off all scroll listener
        $(this.$refs.conversationMessageListEl).off('scroll');
        $(this.$refs.conversationListEl).off('scroll');
    },
    methods: {
        async init() {
            try {
                console.log(1);
                this.loading.conversation = true;
                await Promise.all([
                    this.getChatConfig(),
                    this.DateUtil.checkGoTimeReady()
                ]);

                console.log(2);

                // await this.$_getConversationInfoFromServer(null);
                await this.$_getConversationListFromFirebase();

                console.log(4);
                this.loading.conversation = false;
                this.initialized = true;
                console.log(5);
                await new Promise((resolve) => {
                    console.log(6);
                    let waitForGettingConversationInfo = () => {
                        if (!Object.keys(this.loading.targetInfoList).length) {
                            console.log('dont waiting');
                            resolve();
                        } else {
                            setTimeout(waitForGettingConversationInfo, 1000);
                        }
                    };
                    waitForGettingConversationInfo();
                });
                console.log(7);

                // let filterQuery = stringUtil.getUrlQueries(window.location.href, 'q');
                // if (filterQuery) {
                //     this.filters.q = filterQuery;
                // } else if (this.orderedConversations.length) {
                //     this.$_setCurrentConversation(this.orderedConversations[0]);
                // }

                console.log(8);
                this.$_initLoadMoreMessageList();
                this.$_initLoadMoreConversationList();
                // firebase.database().ref(firebaseRef.userNumberOfUnseenMessage(this.user.id)).set(0);
                if(this.userId){
                    console.log('fake conver',this.userId);
                    let shopInfo = await this.getShopInfoFromUserId(this.userId)
                    console.log('info conver fake ----', shopInfo);
                    if(shopInfo.error == EErrorCode.NO_ERROR) {
                        console.log('---shop info for fake',shopInfo)
                        await new Promise((resolve) => {
                            console.log(6);
                            let waitForGettingConversationFromServer = () => {
                                //get shop info of userId
                                //if any conversation has target is shopinfo(user này đã chat với shop này trước đó)
                                //prior this conversation
                                //if all conversation don't has target is shopinfo(user này chưa từng chat với shop)
                                //create temporary conversation, add to list conversation, prior this conversation
                                if (this.loading.firstLoadConversationFromServer) {
                                    let converSationOfUserIdProps = Object.values(this.conversations).find(e => {
                                        if(e.target.user_id) {
                                            return e.target.user_id == this.userId;
                                        }
                                    })
                                    if(converSationOfUserIdProps) {
                                        converSationOfUserIdProps.orderKey = `1_${Date.now()}`
                                    } else {
                                        console.log('shopinfo for temp--',shopInfo);
                                        let tempConversation = {
                                            conversation_id: null,
                                            conversation_uid: null,
                                            name: null,
                                            last_messages: null,
                                            last_updated_at: null,
                                            lastUpdatedAtStr: null,
                                            lastUpdatedAtTimeStr: null,
                                            lastUpdatedAtDateStr: null,
                                            orderKey: `1_${Date.now()}`,
                                            target: {
                                                avatar_path: shopInfo.data.avatar_path,
                                                id: shopInfo.data.id,
                                                name: shopInfo.data.name,
                                                name_search: shopInfo.data.name_search,
                                                uid: `u${shopInfo.data.id}`,
                                                user_id: shopInfo.data.user_id,
                                                avatar_type: shopInfo.data.avatar_type,
                                            },
                                            number_of_unseen_messages: 0,
                                        }
                                        this.conversations.unshift(tempConversation);
                                    }

                                    this.$_setCurrentConversation(this.orderedConversations[0]);
                                    resolve();
                                } else {
                                    console.log('waitingggggggg get conversation from serve');
                                    setTimeout(waitForGettingConversationFromServer, 1000);
                                }
                            };
                            waitForGettingConversationFromServer();
                        });
                        if(this.firstMessage) {
                            this.$_sendMessage({
                                message: this.firstMessage,
                                message_type: EConversationMessageType.TEXT,
                            });
                        }
                    }
                }
            } catch (e) {
                console.error(e);
            }
        },
        getChatConfig() {
            return new Promise((resolve, reject) => {
                this.Util.post({
                    url: this.routes.config,
                }).done((res) => {
                    if (res.error !== ErrorCode.NO_ERROR) {
                        reject(res.msg);
                        return;
                    }
                    this.error = null;
                    this.resourceUrlPath = res.resource_url_path;
                    this.user = res.current_user;

                    this.userInfoList[this.user.id] = this.user;
                    resolve();
                }).fail(() => {
                    reject();
                });
            });
        },
        async $_getConversationListFromFirebase() {
            await this.FirebaseUtil.checkFirebaseReady();
            console.log('c1');

            return new Promise(async (resolve, reject) => {
                let ref = firebase.database().ref(firebaseRef.userConversationList(this.user.id))
                    .orderByChild('deleted__last_updated_at')
                    .startAt('1_');
                console.log('c1x', firebaseRef.userConversationList(this.user.id));

                this.firebase.refs.push(ref);
                //if load more conversation, just load older conversations
                //than oldest conversation in current conversation list
                if (this.orderedConversations.length) {
                    let x1 = [...this.orderedConversations];
                    console.log('c1x1',x1,x1[x1.length - 1].orderKey)

                    ref = ref.endAt(this.orderedConversations[this.orderedConversations.length - 1].orderKey)
                        .limitToLast(11);
                } else {
                    ref = ref.limitToLast(10);
                }

                let numberOfConversation = 0;
                let numberOfProcessedConversation = 0;
                console.log('c2');
                await new Promise((resolve2) => {
                    ref.once('value', async (snapshot) => {
                        let conversationList = snapshot.val() || {};
                        console.log('c2.1',conversationList);
                        if (!conversationList || !Object.keys(conversationList).length) {
                            console.log('c2.2');
                            resolve();
                        }
                        numberOfConversation = Object.keys(conversationList).length;
                        let waitingForInit = () => {
                            console.log('waiting');
                            if (this.initialized) {
                                //nếu là trường hợp load thêm conversation mà chỉ
                                //lấy được về conversation cũ nhất thì không cần phải
                                //lên server lấy info
                                console.log('----------------hehe',this.orderedConversations.length,numberOfConversation);
                                if(this.orderedConversations.length > 0) {
                                    if(this.orderedConversations[0].name && numberOfConversation <= 1){
                                        console.log('no new conver---');
                                        resolve2();
                                        return;
                                    }
                                }
                                console.log('passssssssssssssssss-----------------------------')
                                this.$_getConversationInfoFromServer(Object.keys(conversationList).map((conversationUid) => this.$_getConversationIdVal(conversationUid)));
                            } else {
                                console.log('timeout from list firebase')
                                setTimeout(waitingForInit, 500);
                            }
                        };

                        waitingForInit();

                        resolve2();
                    });
                });
                console.log('c3');

                ref.on('value', (cSnapshot) => {
                    console.log('c4-have new conversation',cSnapshot.val());
                    let conversations = cSnapshot.val();
                    if (!conversations || !Object.keys(conversations).length) {
                        return resolve();
                    }
                    console.log('c5',conversations);

                    Object.keys(conversations).forEach(async (key) => {
                        console.log('c6');
                        let numberOfProcessedConversationScope = ++numberOfProcessedConversation;
                        let conversation = conversations[key];
                        if (!conversation || conversation.deleted) {
                            console.log('c7');
                            if (numberOfProcessedConversationScope === numberOfConversation) {
                                resolve();
                            }
                            return;
                        }
                        if (oldSnapShotJson[`${key}_1`] === JSON.stringify(conversation)) {
                            if (numberOfProcessedConversationScope === numberOfConversation) {
                                resolve();
                            }
                            return;
                        }
                        oldSnapShotJson[`${key}_1`] = JSON.stringify(conversation);
                        console.log('c8');

                        await this.$_getConversationInfoFromFirebase(key);
                        console.log('c9');
                        if (numberOfProcessedConversationScope === numberOfConversation) {
                            console.log('c10');
                            resolve();
                        }
                        if (this.initialized) {
                            console.log('c11');
                            this.$_getConversationInfoFromServer([this.$_getConversationIdVal(key)]);
                        }
                        console.log('c12');
                    });
                }, (error) => {
                    console.error(error);
                });

                if (!numberOfConversation) {
                    console.log('c13')
                    resolve();
                }
            }, (err) => {
                reject(err);
            });
        },
        $_setCurrentConversation(conversation) {
            console.log('$_setCurrentConversation')
            console.log('s1');
            this.loading.currentConversation = false;
            if (!conversation) {
                console.log('s2');
                this.messages = [];
                this.currentConversation = {};
            }
            //if exist current conversation and current conversation != new current conversation
            //off ref get new message of current conversation and set new ref for new current conversation
            if (this.currentConversation &&
                this.currentConversation.conversation_id &&
                this.currentConversation.conversation_id !== conversation.conversation_id) {
                console.log('s3');
                firebase.database()
                    .ref(firebaseRef.messageByConversation(this.currentConversation.conversation_uid))
                    .off();
            }
            if (!conversation) {
                console.log('s4');
                return;
            }
            console.log('s5');
            if (this.currentConversation.conversation_id !== conversation.conversation_id || this.currentConversation.target.id !== conversation.target.id) {
                console.log('s6');
                this.messages = [];
                this.currentConversation = conversation;
                // nếu là Temp Conversation không cần lên firebase lấy tin nhắn
                if(!conversation.conversation_id){
                    console.log('current is fake')
                    return;
                }
                this.$_getCurrentConversationMessages();
            }
        },
        $_getConversationInfoFromFirebase(conversationUid) {
            return new Promise((resolve) => {
                console.log('f1');
                if (!conversationUid) {
                    return;
                }

                let childRef = firebase.database().ref(firebaseRef.userConversation(this.user.id, conversationUid));
                this.firebase.refs.push(childRef);
                console.log('f2', childRef);
                childRef.on('value', (snapshot) => {
                    console.log('f3');
                    let conversation = snapshot.val();
                    console.log('conv-firebase-------------------------',conversation);
                    if (!conversation) {
                        resolve();
                        return;
                    }

                    console.log('f4');

                    // Last message of conversation
                    let last_message = '';
                    if (conversation.last_messages && conversation.last_messages.message_type === 2) {
                        last_message = `[Hình ảnh]`;
                    } else if(conversation.last_messages && conversation.last_messages.message_type === 3){
                        last_message = `[File]`;
                    } else if (conversation.last_messages) {
                        last_message = conversation.last_messages.content;
                    }

                    // check if new added conversation is in conversation list
                    let existedConversation = this.conversations.find((item) => {
                        return item.conversation_uid === snapshot.key;
                    });
                    console.log('f5', existedConversation);
                    if (existedConversation) {
                        console.log('f6');
                        let index = this.conversations.indexOf(existedConversation);
                        // if conversation is deleted, remove it from conversation list
                        if (conversation.deleted) {
                            console.log('f7');
                            this.$_updateNumberOfUnseenMessage(existedConversation, true);
                            this.conversations.splice(index, 1);
                            // if deleted conversation is current conversation, set current
                            // conversation to be first conversation in conversation list
                            //
                            // if conversation list is empty, set current conversation to empty
                            if (existedConversation.conversation_id === this.currentConversation.conversation_id) {
                                if (this.conversations.length) {
                                    this.$_setCurrentConversation(this.conversations[0]);
                                } else {
                                    this.$_setCurrentConversation(null);
                                }
                            }
                            resolve(existedConversation);
                            return;
                        }
                        console.log('f8');
                        if (!conversation.last_messages) {
                            console.log('f9');
                            resolve(existedConversation);
                            return;
                        }
                        console.log('f10');
                        // if conversation is not deleted, set new value
                        this.conversations[index].last_messages = last_message;
                        this.conversations[index].lastUpdatedAtStr = this.$_formatTimestamp(conversation.last_messages.timestamp);
                        this.conversations[index].lastUpdatedAtTimeStr = this.$_formatTime(conversation.last_messages.timestamp);
                        this.conversations[index].lastUpdatedAtDateStr = this.$_formatDate(conversation.last_messages.timestamp);
                        this.conversations[index].orderKey = conversation.deleted__last_updated_at;
                        this.$_updateNumberOfUnseenMessage(existedConversation);

                    } else if (!conversation.deleted) {
                        // if conversation is not in conversation list yet, add to top of conversation list

                        existedConversation = {
                            conversation_id: this.$_getConversationIdVal(snapshot.key),
                            conversation_uid: snapshot.key,
                            name: null,
                            last_messages: last_message,
                            lastUpdatedAtStr: conversation.last_messages ? this.$_formatTimestamp(conversation.last_messages.timestamp) : null,
                            lastUpdatedAtTimeStr: conversation.last_messages ? this.$_formatTime(conversation.last_messages.timestamp) : null,
                            lastUpdatedAtDateStr: conversation.last_messages ? this.$_formatDate(conversation.last_messages.timestamp) : null,
                            orderKey: conversation.deleted__last_updated_at,
                            target: {
                                id: null,
                                uid: null,
                                user_id: null,
                                name: null,
                                phone: null,
                                avatar_path: null,
                            },
                            number_of_unseen_messages: 0,
                        };
                        this.conversations.unshift(existedConversation);
                        console.log('f11');
                        this.$_updateNumberOfUnseenMessage(existedConversation);
                        console.log('f12');
                    }

                    resolve(existedConversation);

                    this.$nextTick(() => {
                        if (this.conversations.length && !Object.keys(this.currentConversation).length) {
                            console.log('nexttich----')
                            this.$_setCurrentConversation(this.orderedConversations[0]);
                        }
                    });

                }, (error) => {
                    console.error(error);
                    resolve();
                });
            });
        },
        // if conversation list is empty, get first 15 conversation
        $_getConversationInfoFromServer(conversationIdList = [], listeningForFirebaseChanges = true) {
            console.log('lít------------',conversationIdList);
            console.log('--------1',!conversationIdList);
            if (!conversationIdList && conversationIdList !== null) {
                console.log('-xxx------------------------');
                return Promise.reject();
            }
            return new Promise((resolve, reject) => {
                if (Array.isArray(conversationIdList)) {
                    conversationIdList.forEach((conversationId) => {
                        // only show loading when user info is not ready
                        let conversation = this.conversations.find(item => item.conversation_id === conversationId);
                        if (conversation && !conversation.target.id && !this.loading.firstLoadConversationFromServer) {
                            this.$set(this.loading.targetInfoList, `${conversationId}`, true);
                        }
                    });
                }
                this.Util.post({
                    url: this.routes.info,
                    data: {
                        conversation_id_list: conversationIdList,
                        ...this.filters,
                    },
                }).done((res) => {
                    if (res.error !== ErrorCode.NO_ERROR) {
                        reject(res.msg);
                        return;
                    }
                    console.log('xiiiiiiiiiiiiiiiiiiii-------------------',res)

                    res.data.forEach((item) => {
                        // if (item.shop_id && !this.shopInfoList[item.shop_id]) {
                        //     this.shopInfoList[item.user_id] = item.target;
                        // }
                        // if (!item.shop_id  && !this.userInfoList[item.user_id]) {
                        //     this.userInfoList[item.user_id] = item.target;
                        // }

                        if(!this.targetInfoList[item.conversation_id]){
                            this.targetInfoList[item.conversation_id] = item.target;
                        }

                        // set current conversation if there is no current one
                        // this.$nextTick(() => {
                        //     if (!this.currentConversation.conversation_id && this.orderedConversations.length) {
                        //         this.$_setCurrentConversation(this.orderedConversations[0]);
                        //     }
                        // });


                        let filteredConversation = this.conversations.find(c => c.conversation_id === item.conversation_id);
                        if (filteredConversation) {
                            console.log('updateeeeeeeeeeeeeeeee conver info from server',filteredConversation.conversation_id)
                            filteredConversation.name = item.conversation_name;
                            filteredConversation.target = {
                                ...item.target,
                                uid: `u${item.user_id}`
                            }
                        }
                        else{
                            console.log('addd conver info from server')
                            this.conversations.unshift({
                                conversation_id: item.conversation_id,
                                conversation_uid: `c${item.conversation_id}`,
                                name: item.conversation_name,
                                last_messages: null,
                                last_updated_at: null,
                                lastUpdatedAtStr: null,
                                lastUpdatedAtTimeStr: null,
                                lastUpdatedAtDateStr: null,
                                orderKey: `0_${Date.now()}`,
                                target: {
                                    ...item.target,
                                    uid: `u${item.user_id}`,
                                },
                                number_of_unseen_messages: 0,
                            });
                        }
                        // remove conversation with null id
                        // console.log('b1');
                        // this.conversations.forEach((conversation) => {
                        //     // remove conversation with null id
                        //     /*if (conversation.conversation_id === null && conversation.customer.id === item.customer.id) {
                        //         this.conversations.splice(this.conversations.indexOf(conversation), 1);
                        //     } else*/
                        //     console.log('b2');
                        //     if (conversation.interest_id === item.interest_id && conversation.isApproved !== item.isApproved) {
                        //         console.log('b2.1');
                        //         let waitForConversationToShow = () => {
                        //             console.log('b2.2');
                        //             let newConversation = this.conversations.find(c => c.conversation_id === item.id);
                        //             if (!newConversation) {
                        //                 console.log('b2.3');
                        //                 setTimeout(() => {
                        //                     waitForConversationToShow();
                        //                 }, 500);
                        //                 return;
                        //             }
                        //             console.log('b2.4');
                        //
                        //             // remove old conversation and set current conversation to new conversation
                        //             let indexOfOldConversation = this.conversations.indexOf(conversation);
                        //             if (indexOfOldConversation > -1) {
                        //                 this.conversations.splice(indexOfOldConversation, 1);
                        //                 console.log('b2.5');
                        //             }
                        //
                        //             if (this.currentConversation === conversation) {
                        //                 this.$_setCurrentConversation(newConversation);
                        //             }
                        //         };
                        //         waitForConversationToShow();
                        //         return;
                        //     }
                        //     if (conversation.conversation_id === item.id || conversation.interest_id === item.interest_id) {
                        //         conversation.name = item.name;
                        //         conversation.interest_id = item.interest_id;
                        //         conversation.isApproved = item.isApproved;
                        //         conversation.isCanceled = item.isCanceled;
                        //         console.log('b3');
                        //         // update user info to new added conversation
                        //         Object.keys(item.customer || {}).forEach((key) => {
                        //             conversation.customer[key] = item.customer[key];
                        //         });
                        //         if (item.customer) {
                        //             conversation.customer.uid = `u${item.customer.id}`;
                        //         }
                        //         console.log('b4');
                        //
                        //         if (listeningForFirebaseChanges) {
                        //             let memberRef = firebase.database().ref(firebaseRef.conversationMemberList(`c${item.id}`));
                        //             memberRef.off();
                        //             this.firebase.refs.push(memberRef);
                        //
                        //             memberRef.on('child_added', async (snapshot) => {
                        //                 let member = snapshot.val();
                        //                 if (member && member.deleted_conversation) {
                        //                     return;
                        //                 }
                        //                 let currentMemberInConversation = [`u${this.user.id}`, conversation.customer.uid];
                        //                 if (currentMemberInConversation.includes(snapshot.key)) {
                        //                     return;
                        //                 }
                        //
                        //                 this.loading.staffFilter = conversation.conversation_id === this.currentConversation.conversation_id;
                        //                 this.loading.chatInput = conversation.conversation_id === this.currentConversation.conversation_id;
                        //                 await this.$_getConversationInfoFromServer([conversation.conversation_id], false);
                        //                 this.loading.staffFilter = false;
                        //                 this.loading.chatInput = false;
                        //             });
                        //         }
                        //     }
                        // });
                    });

                    resolve();

                    // this.$nextTick(() => {
                    //     resolve(res.data);
                    // });
                }).fail((res) => {
                    reject(res);
                }).always(() => {
                    if(!this.loading.firstLoadConversationFromServer) {
                        this.$set(this.loading,'firstLoadConversationFromServer', true )
                    }
                    if (Array.isArray(conversationIdList)) {
                        console.log('loading target',Object.keys(this.loading.targetInfoList),conversationIdList);
                        conversationIdList.forEach((conversationId) => {
                            this.$set(this.loading.targetInfoList, `${conversationId}`, false);
                            delete this.loading.targetInfoList[`${conversationId}`];
                            if(Object.keys(this.loading.targetInfoList).length == 0) {
                                this.$set(this.loading, 'targetInfoList', {});
                            }
                        });
                    }
                });
            });
        },
        $_updateNumberOfUnseenMessage(conversationInfo, force = false) {
            console.log('f112');
            let ref = firebase.database().ref(firebaseRef.memberNumberOfUnseenMessage(conversationInfo.conversation_uid, this.user.id));
            ref.off();
            this.firebase.refs.push(ref);
            ref.on('value', (snapshot) => {
                console.log('updateNOUM-----')
                if (force || this.currentConversation.conversation_id === conversationInfo.conversation_id) {
                    this.$_setConversationSeen(conversationInfo);
                } else {
                    conversationInfo.number_of_unseen_messages = snapshot.val() || 0;
                }
            });
        },

        //if scroll of message list on top, get more messages from current conversation
        $_initLoadMoreMessageList() {
            $(this.$refs.conversationMessageListEl).scroll(() => {
                console.log('scroll',this.$refs.conversationMessageListEl.scrollTop);
                if (this.$refs.conversationMessageListEl.scrollTop === 0 && this.currentConversation.conversation_id != null) {
                    this.$_getMoreMessages(this.currentConversation.conversation_id);
                }
            });
        },

        $_initLoadMoreConversationList() {
            let el = this.$refs.conversationListEl;
            $(el).scroll(() => {
                if (el.scrollHeight - el.clientHeight === this.$refs.conversationListEl.scrollTop) {
                    this.$_getConversationListFromFirebase();
                }
            });
        },
        /*$_checkCustomerConversationExists(conversation) {
            return new Promise((resolve) => {
                this.Util.post({
                    url: this.routes.conversationExists,
                    data: {
                        interest_id: conversation.interest_id,
                    }
                }).done((res) => {
                    resolve(res);
                });
            });
        },*/
        async $_getCurrentConversationMessages() {

            console.log('get curent conver mess')
            if(!this.currentConversation.conversation_id){
                console.log('fake convere');
                return ;
            }
            $('.loading_image_messages').removeClass('d-none').addClass('d-flex');
            this.messagesPage = 1;
            this.loading.currentConversation = true;

            try {
                await new Promise((resolve, reject) => {
                    this.Util.get({
                        url: this.routes.permission,
                    }).done((res) => {
                        if (res.error !== ErrorCode.NO_ERROR) {
                            reject(res.msg);
                            return;
                        }
                        if (res.hasPermission) {
                            resolve();
                        } else {
                            reject(res.msg);
                        }
                    });
                });
            } catch (e) {
                // this.error.chat = typeof e === 'string' ? e : this.$t('common.error.unknown');
                console.error(e);
                this.loading.currentConversation = false;
                return;
            }

            await this.FirebaseUtil.checkFirebaseReady();
            let ref = firebase.database().ref(firebaseRef.messageByConversation(this.currentConversation.conversation_uid))
                .orderByKey()
                .limitToLast(15);
            this.firebase.refs.push(ref);
            await new Promise((resolve) => {
                console.log('m0', ref);
                ref.on('child_added', (snapshot) => {
                    console.log('m1');
                    if (!this.messages.every((item) => item.id !== snapshot.key)) {
                        console.log('m2');
                        resolve();
                        return;
                    }
                    console.log('m3');
                    let childData = snapshot.val();

                    let message = {
                        id: snapshot.key,
                        message: null,
                        message_type: childData.message_type,
                        receiver_seen: childData.receiver_seen,
                        from_user_id: childData.from_user_id,
                        createdAtStr: this.$_formatTimestamp(childData.timestamp),
                        createdAtTimeStr: this.$_formatTime(childData.timestamp),
                        createdAtDateStr: this.$_formatDate(childData.timestamp),
                        timestamp: childData.timestamp,
                    };
                    if (message.message_type === EConversationMessageType.IMAGE) {
                        message.message = Array.isArray(childData.content) ? childData.content : childData.content.split(",");
                        message.uploaded = true;
                    } else if(message.message_type === EConversationMessageType.FILE) {
                        message.message = Array.isArray(childData.content) ? childData.content : childData.content.split(",");
                        message.uploaded = true;
                    } else {
                        message.message = childData.content;
                    }

                    this.messages.push(message);

                    this.$nextTick(() => {
                       $(this.$refs.conversationMessageListEl).animate({
                           scrollTop: $(this.$refs.conversationMessageListEl).get(0).scrollHeight
                       }, 0);
                    });
                    console.log('m4');
                    resolve();
                });
            });
            console.log('set cur scroll', this.$refs.conversationMessageListEl.scrollTop);
            console.log('m5');
            this.$_setConversationSeen(this.currentConversation);
            $('.loading_image_messages').removeClass('d-flex').addClass('d-none');
            this.loading.currentConversation = false;
            this.$nextTick(() => {
                $(this.$refs.conversationMessageListEl).animate({
                    scrollTop: $(this.$refs.conversationMessageListEl).get(0).scrollHeight
                }, 0);
            });
        },
        $_getMoreMessages() {
            if (!this.currentConversation.conversation_uid) {
                return;
            }
            //don't load more message if not loaded yet
            if(this.loading.currentConversation) {
                return;
            }
            console.log('get more messs--',this.currentConversation.conversation_uid);
            this.messagesPage++;
            this.loading.currentConversationMessage = true;
            $('.loading_image_messages').removeClass('d-none').addClass('d-flex');
            let ref = firebase.database().ref(firebaseRef.messageByConversation(this.currentConversation.conversation_uid))
                .orderByKey()
                .limitToLast(this.messagesPage * 15);
            this.firebase.refs.push(ref);
            ref.once('value', (data) => {
                let childData = data.val();
                let messages = [];
                Object.keys(childData).forEach((key) => {
                    if (!this.messages.every((item) => item.id !== key)) {
                        return;
                    }

                    let message = {
                        id: key,
                        message: null,
                        message_type: childData[key].message_type,
                        receiver_seen: childData[key].receiver_seen,
                        from_user_id: childData[key].from_user_id,
                        createdAtStr: this.$_formatTimestamp(childData[key].timestamp),
                        createdAtTimeStr: this.$_formatTime(childData[key].timestamp),
                        createdAtDateStr: this.$_formatDate(childData[key].timestamp),
                        timestamp: childData[key].timestamp,
                    };
                    if (message.message_type === EConversationMessageType.IMAGE) {
                        message.message = Array.isArray(childData[key].content) ? childData[key].content : childData[key].content.split(",");
                        message.uploaded = true;
                    } else {
                        message.message = childData[key].content;
                    }

                    messages.push(message);
                });
                messages.reverse().forEach((message) => this.messages.unshift(message));
                $('.loading_image_messages').removeClass('d-flex').addClass('d-none');
                this.loading.currentConversationMessage = false;
                this.loading.currentConversation = false;
                if (messages.length) {
                    $(this.$refs.conversationMessageListEl).animate({
                        scrollTop: 100
                    }, 0);
                }
            });
        },
        $_setConversationSeen(conversation) {
            conversation.number_of_unseen_messages = 0;
            console.log('set current conver---');

            firebase.database().ref(firebaseRef.memberNumberOfUnseenMessage(conversation.conversation_uid, this.user.id))
                .once('value', (snapshot) => {
                    let numberOfSeenMessage = snapshot.val() || 0;
                    console.log('seen-----',numberOfSeenMessage);

                    //set unseenMessage in this conversation
                    firebase.database().ref(firebaseRef.memberNumberOfUnseenMessage(conversation.conversation_uid, this.user.id))
                        .transaction(function (value) {
                        return value > numberOfSeenMessage ? value - numberOfSeenMessage : 0;
                    });

                    //set total unseenMessage of user
                    firebase.database().ref(firebaseRef.userNumberOfUnseenMessage(this.user.id))
                        .transaction(function (value) {
                            return value > numberOfSeenMessage ? value - numberOfSeenMessage : 0;
                        });
                });
        },
        async $_handleUploadImageFile(evt) { // upload image to sever then get link path
            if (!evt || !evt.target || !evt.target.files || !evt.target.files.length) {
                return;
            }

            for (let i = 0; i < evt.target.files.length; i++) {
                let image = {
                    file: evt.target.files[i],
                    src: null,
                    loading: true,
                };
                this.chatInput.images.push(image);
                this.FileUtil.fileToUrl(image.file).then((url) => {
                    image.src = url;
                    image.loading = false;
                });
            }

            $(evt.target).val('');
        },
        async $_handleUploadFile(evt) { // upload file to sever then get link path
            if (!evt || !evt.target || !evt.target.files || !evt.target.files.length) {
                return;
            }
            let imgType = ['image/jpeg', 'image/jpg', 'image/png'];
            let acceptType = ['application/x-zip-compressed', 'audio/mpeg', 'application/pdf',
                'text/plain', 'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']

            //hai định dạng .doc và .docx
            //'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            if(imgType.indexOf(evt.target.files[0].type) !== -1 ) {
                let image = {
                    file: evt.target.files[0],
                    src: null,
                    loading: true,
                };
                this.chatInput.images.push(image);
                this.FileUtil.fileToUrl(image.file).then((url) => {
                    image.src = url;
                    image.loading = false;
                });
                $(evt.target).val('');
            } else {
                if(acceptType.indexOf(evt.target.files[0].type) === -1) {
                    bootbox.alert('File tải lên không hợp lệ');
                    return false;
                }
                let file = {
                    file: evt.target.files[0],
                    src: null,
                    loading: true,
                    name: '',
                    size: '',
                    type: '',
                };
                this.chatInput.file = file;
                this.FileUtil.fileToUrl(file.file).then((url) => {
                    file.src = url;
                    file.loading = false;
                    file.size = this.readableFileSizeString(file.file.size);
                    file.name = file.file.name;
                    file.type = file.file.type;
                });
            }
            $(evt.target).val('');
        },
        $_reviewImage(imgSrc) {
            this.reviewImageUrl = imgSrc;
            $(this.$refs.reviewImageModalEl).modal('show');
        },
        $_showUserInfo(user) {
            this.reviewUser = {...user};
            $(this.$refs.reviewUserModalEl).modal('show');
        },
        /**
         * send message
         * @param message add this param to resend a message
         */
        async $_sendMessage(message = null) {
            console.log('---------------------------send click')

            //if this temp conversation, create conversation before send message
            // if(!this.currentConversation.conversation_id){
            //     console.log('falseeeeeeeeeeeeeeeeeeeeee---------------');
            //     this.createConversation();
            //     return;
            //     //post api create conversation, get new conversation
            //
            //
            //     // create conversation on Firebase(chat_by_user,member,conversation, messages_by_conversation)
            //
            //     //after get new conversation, replace temp conversation with new conversaton
            //
            //
            //
            // }
            this.currentTab = 'messages';
            if (!message) {
                message = {
                    message: this.chatInput.text,
                    message_type: EConversationMessageType.TEXT,
                };
                if ((!message.message || !message.message.trim()) &&
                    !this.chatInput.images.length &&
                    !this.chatInput.file) {
                    this.$refs.chatTextInputEl.focus();
                    return;
                }
            }

            this.loading.chatInput = true;

            let currentConversation = this.currentConversation;
            let getConversationId = () => new Promise((resolve, reject) => {
                if (!this.currentConversation.conversation_id) {
                    this.createConversation()
                        .then((conversationId) => {
                            currentConversation.conversation_id = conversationId;
                            currentConversation.conversation_uid = `c${conversationId}`;
                            this.$_getCurrentConversationMessages();
                            resolve(conversationId);
                        });
                    return;
                }
                resolve(currentConversation.conversation_id);
            });

            // post image to server
            let memberIds = [this.user.id];
            //nếu target là shop thì target.id là id cửa hàng, target.user_id là id của chủ shop,
            //trên firebase sẽ là chat giữa 2 user không phải chat giữa user và shop
            if (this.currentConversation.target && this.currentConversation.target.user_id) {
                memberIds.push(this.currentConversation.target.user_id);
            } else if (this.currentConversation.target && this.currentConversation.target.id) {
                memberIds.push(this.currentConversation.target.id);
            }
            // console.log('------------------memberIds',memberIds)
            // this.loading.chatInput = false;
            // return;

            /* - Nếu chat input có chứa image thì xử lí gửi image message
               - Trường hợp message.message_type === image là trường hợp trước đó gửi ảnh lỗi,
              khi gửi ảnh lại sẽ gọi methods sendMessage kèm với ảnh cần gửi lại ( trong biến message) */
            if (this.chatInput.images.length || message.message_type === EConversationMessageType.IMAGE) {
                let newImageMessage;
                let conversationId = await getConversationId();
                if (message.message_type === EConversationMessageType.IMAGE) {
                    newImageMessage = message;
                    newImageMessage.loading = true;
                    newImageMessage.error = false;
                } else {
                    let now = Date.now();
                    newImageMessage = {
                        id: this.$_getNewMessageKey(conversationId),
                        message: [],
                        message_type: EConversationMessageType.IMAGE,
                        receiver_seen: false,
                        from_user_id: this.user.id,
                        createdAtStr: this.$_formatTimestamp(now),
                        createdAtTimeStr: this.$_formatTime(now),
                        createdAtDateStr: this.$_formatDate(now),
                        timestamp: Date.now(),
                        imageList: [...this.chatInput.images],
                        uploadedPercent: 0,
                        uploaded: false,
                        loading: true,
                        error: false,
                    };
                    this.messages.push(newImageMessage);
                    this.chatInput.images = [];
                    this.$nextTick(() => {
                        $(this.$refs.conversationMessageListEl).animate({
                            scrollTop: $(this.$refs.conversationMessageListEl).get(0).scrollHeight
                        }, 800);
                    });
                }

                // console.log('newImageMessage--------------------------',newImageMessage);
                // this.loading.chatInput = false;
                // return;

                // file will remove when uploaded, so I use file to check if image has uploaded
                let continueToSendImageMessage = true;
                if (!newImageMessage.uploaded) {
                    // console.log('newImageMessage--------------------------',newImageMessage);
                    // this.loading.chatInput = false;
                    // return;
                    let formData = new FormData();
                    newImageMessage.imageList.forEach((image) => {
                        formData.append('image[]', image.file);
                    });
                    try {
                        newImageMessage.message = await new Promise((resolve, reject) => {
                            this.Util.post({
                                url: this.routes.saveImage,
                                data: formData,
                                processData: false,
                                contentType: false,
                                xhr: () => {
                                    let xhr = new window.XMLHttpRequest();
                                    // Upload progress
                                    let cb = (evt) => {
                                        if (evt.lengthComputable) {
                                            let completePercent = evt.loaded / evt.total;
                                            if (completePercent === 1) {
                                                newImageMessage.uploadedPercent = 0.8;
                                            } else {
                                                newImageMessage.uploadedPercent = Math.round(completePercent * 80) / 100;
                                            }
                                        }
                                    };
                                    xhr.upload.addEventListener("progress", cb, false);
                                    xhr.addEventListener("progress", cb, false);

                                    return xhr;
                                },
                            }).done((res) => {
                                if (res.error !== ErrorCode.NO_ERROR) {
                                    reject(res.msg);
                                }
                                resolve(res.data);
                            }).fail((res) => {
                                reject(res);
                            });
                        });
                        newImageMessage.uploaded = true;
                        newImageMessage.imageList = [];
                    } catch (e) {
                        console.error(e);
                        newImageMessage.error = true;
                        this.loading.chatInput = false;
                        continueToSendImageMessage = false;
                    }
                }

                if (continueToSendImageMessage) {
                    try {
                        newImageMessage.uploadedPercent = 0.9;
                        await this.$_pushMessageToFirebase(newImageMessage.message.toString(), newImageMessage.message_type, conversationId, memberIds, newImageMessage.id);
                        newImageMessage.uploadedPercent = 1;
                        newImageMessage.loading = false;
                    } catch (e) {
                        console.error(e);
                        newImageMessage.error = true;
                        this.loading.chatInput = false;
                    }
                }

                newImageMessage.loading = false;
            }

            /* - Nếu chat input có chứa file thì xử lí gửi file message
               - Trường hợp messae.message_type === file là trường hợp trước đó gửi file lỗi,
                 khi gửi file lại sẽ gọi methods sendMessage kèm với file cần gửi lại ( trong biến message) */
            if (!!this.chatInput.file || message.message_type === EConversationMessageType.FILE) {
                let newFileMessage;
                let conversationId = await getConversationId();
                if (message.message_type === EConversationMessageType.FILE) {
                    newFileMessage = message;
                    newFileMessage.loading = true;
                    newFileMessage.error = false;
                } else {
                    let now = Date.now();
                    newFileMessage = {
                        id: this.$_getNewMessageKey(conversationId),
                        message: [],
                        message_type: EConversationMessageType.FILE,
                        receiver_seen: false,
                        from_user_id: this.user.id,
                        createdAtStr: this.$_formatTimestamp(now),
                        createdAtTimeStr: this.$_formatTime(now),
                        createdAtDateStr: this.$_formatDate(now),
                        timestamp: Date.now(),
                        file: {...this.chatInput.file},
                        uploadedPercent: 0,
                        uploaded: false,
                        loading: true,
                        error: false,
                    };
                    this.messages.push(newFileMessage);
                    this.chatInput.file = null;
                    this.$nextTick(() => {
                        $(this.$refs.conversationMessageListEl).animate({
                            scrollTop: $(this.$refs.conversationMessageListEl).get(0).scrollHeight
                        }, 800);
                    });
                }

                // console.log('newImageMessage--------------------------',newImageMessage);
                // this.loading.chatInput = false;
                // return;

                // file will remove when uploaded, so I use file to check if image has uploaded
                let continueToSendFileMessage = true;
                if (!newFileMessage.uploaded) {
                    // console.log('newImageMessage--------------------------',newImageMessage);
                    // this.loading.chatInput = false;
                    // return;
                    let formData = new FormData();
                    formData.append('file', newFileMessage.file.file);
                    try {
                        let linkFile = await new Promise((resolve, reject) => {
                            this.Util.post({
                                url: this.routes.saveFile,
                                data: formData,
                                processData: false,
                                contentType: false,
                                xhr: () => {
                                    let xhr = new window.XMLHttpRequest();
                                    // Upload progress
                                    let cb = (evt) => {
                                        if (evt.lengthComputable) {
                                            let completePercent = evt.loaded / evt.total;
                                            if (completePercent === 1) {
                                                newFileMessage.uploadedPercent = 0.8;
                                            } else {
                                                newFileMessage.uploadedPercent = Math.round(completePercent * 80) / 100;
                                            }
                                        }
                                    };
                                    xhr.upload.addEventListener("progress", cb, false);
                                    xhr.addEventListener("progress", cb, false);

                                    return xhr;
                                },
                            }).done((res) => {
                                if (res.error !== ErrorCode.NO_ERROR) {
                                    reject(res.msg);
                                }
                                resolve(res.data);
                            }).fail((res) => {
                                reject(res);
                            });
                        });
                        newFileMessage.message = [linkFile,newFileMessage.file.name, newFileMessage.file.type, newFileMessage.file.size];
                        newFileMessage.uploaded = true;
                        newFileMessage.file = null;
                    } catch (e) {
                        console.error(e);
                        newFileMessage.error = true;
                        this.loading.chatInput = false;
                        continueToSendFileMessage = false;
                    }
                }

                console.log('----newfile', newFileMessage);

                if (continueToSendFileMessage) {
                    try {
                        newFileMessage.uploadedPercent = 0.9;
                        await this.$_pushMessageToFirebase(newFileMessage.message.toString(), newFileMessage.message_type, conversationId, memberIds, newFileMessage.id);
                        newFileMessage.uploadedPercent = 1;
                        newFileMessage.loading = false;
                    } catch (e) {
                        console.error(e);
                        newFileMessage.error = true;
                        this.loading.chatInput = false;
                    }
                }

                newFileMessage.loading = false;
            }


            if (message.message === '' || typeof message.message !== 'string') {
                this.loading.chatInput = false;
                setTimeout(() => {
                    this.$refs.chatTextInputEl.focus();
                }, 0);
                return;
            }
            let conversationId = await getConversationId();
            console.log('before push message to firebase',  conversationId, message);
            await this.$_pushMessageToFirebase(message.message, message.message_type, conversationId, memberIds);
            if (!message.id) {
                this.chatInput.text = '';
            }
            $(this.$refs.conversationMessageListEl).animate({
                scrollTop: $(this.$refs.conversationMessageListEl).get(0).scrollHeight
            }, 800);
            this.loading.chatInput = false;
            setTimeout(() => {
                this.$refs.chatTextInputEl.focus();
            }, 0);
        },
        createConversation() {
            return new Promise((resolve) => {
                this.Util.post({
                    url: this.routes.create,
                    data: {
                        userId: this.currentConversation.target.user_id,
                        shopId: this.currentConversation.target.id,
                        shopName: this.currentConversation.target.name,
                    }
                }).done((res) => {
                    resolve(res.conversationId);
                });
            });
        },
        $_getNewMessageKey(conversationId) {
            return firebase.database().ref(`messages_by_conversation/c${conversationId}`).push().key;
        },
        $_pushMessageToFirebase(content, message_type, conversationId_now, members = [], newMessageKey = null, asUserId = null) {
            return new Promise((resolve) => {
                let conversationId = this.$_getConversationIdVal(conversationId_now);
                if (!newMessageKey) {
                    newMessageKey = this.$_getNewMessageKey(conversationId);
                }

                let now = GoTime.now();

                console.log('p1');
                console.debug();
                firebase.database().ref(`messages_by_conversation/c${conversationId}/${newMessageKey}`).set({
                    from_user_id: asUserId || this.user.id,
                    uid: asUserId || this.user.id,
                    content: content,
                    timestamp: firebase.database.ServerValue.TIMESTAMP,
                    message_type: message_type
                });

                console.log('p2', members);
                members.forEach((userId, index) => {
                    console.log('p3', userId, index);
                    let updateData = {
                        [`chats_by_user/u${userId}/_conversation/c${conversationId}/deleted`]: false,
                        [`chats_by_user/u${userId}/_conversation/c${conversationId}/last_updated_at`]: firebase.database.ServerValue.TIMESTAMP,
                        [`chats_by_user/u${userId}/_conversation/c${conversationId}/deleted__last_updated_at`]: `1_${now}`,
                        [`chats_by_user/u${userId}/_conversation/c${conversationId}/last_messages`]: {
                            content: content,
                            timestamp: firebase.database.ServerValue.TIMESTAMP,
                            message_type: message_type
                        },
                        [`chats_by_user/u${userId}/_all_conversation`]: {
                            conversation_id: conversationId,
                            from_user_id: this.user.id,
                            last_updated_at: firebase.database.ServerValue.TIMESTAMP,
                            last_messages: {
                                content: content,
                                timestamp: firebase.database.ServerValue.TIMESTAMP,
                                message_type: message_type
                            }
                        }
                    };
                    console.log('ppppppp', updateData);
                    firebase.database().ref().update(updateData);

                    console.log('p4');
                    if (userId === this.user.id) {
                        return;
                    }
                    firebase.database().ref(`members/c${conversationId}/u${userId}/number_of_unseen_messages`).transaction(function (value) {
                        return (value || 0) + 1;
                    });
                    firebase.database().ref(`chats_by_user/u${userId}/number_of_unseen_messages`).transaction(function (value) {
                        return (value || 0) + 1;
                    });
                });

                /*this.Util.post({
                    url: this.routes.chat_notification(conversationId),
                    data: {
                        'chat-content': message_type === EConversationMessageType.TEXT ? content : '[Hình ảnh]',
                    }
                });*/
                resolve();
            });
        },
        $_getConversationIdVal(conversationId) {
            return conversationId ? Number(conversationId.toString().replace('c', '')) : null;
        },
        $_formatTimestamp(timestamp) {
            let date = new Date(parseInt(timestamp));
            return `${dateUtil.getTimeString(date)}&#160;${dateUtil.getDateString(date, '/', true)}`;
        },
        $_formatTime(timestamp) {
            let date = new Date(parseInt(timestamp));
            return `${dateUtil.getTimeString(date)}`
        },
        $_formatDate(timestamp) {
            let date = new Date(parseInt(timestamp));
            return `${dateUtil.getDateString(date)}`
        },
        $_showTimeOrDate(timestamp, timeStr, dateStr) {
            return dateStr !== this.today && timestamp < this.todayTimestamp ? dateStr : timeStr;
        },

        getShopInfoFromUserId(ownerUserId) {
            return new Promise((resolve) => {
                this.Util.get({
                    url: this.routes.shopInfo,
                    data: {
                        ownerUserId: ownerUserId,
                    }
                }).done((res) => {
                    resolve(res);
                });
            });
        },
        readableFileSizeString(fileSizeInBytes) {
            let i = -1;
            let byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
            do {
                fileSizeInBytes = fileSizeInBytes / 1024;
                i++;
            } while (fileSizeInBytes > 1024);

            return Math.max(fileSizeInBytes, 0.1).toFixed(2) + byteUnits[i];
        }
    }
}
</script>

<style scoped>
#chat-user >>> .vs__search {
    margin-top: 9px;
}
</style>
