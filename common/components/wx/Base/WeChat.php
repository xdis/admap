<?php
namespace common\components\wx\Base;

/**
 * 第三方api微信地址匹配
 */
class WeChat
{

    function wx_array()
    {
        return array(
            // 微信平台
            'url' => array(
                // 获取access_token (有效期2小时)
                'getAccessToken' => 'https://api.weixin.qq.com/cgi-bin/token',
                
                // 获取jsapi_ticket (有效期2小时)
                'getJsApiTicket' => 'https://api.weixin.qq.com/cgi-bin/ticket/getticket',
                
                // 获取微信服务器IP地址
                'getCallbackIp' => 'https://api.weixin.qq.com/cgi-bin/getcallbackip',
                
                // 消息推送
                'sendMsg' => 'https://api.weixin.qq.com/cgi-bin/message/custom/send',
                
                // 自定义菜单创建
                'createMenu' => 'https://api.weixin.qq.com/cgi-bin/menu/create',
                
                // 自定义菜单查询
                'getMenu' => 'https://api.weixin.qq.com/cgi-bin/menu/get',
                
                // 自定义菜单删除
                'deleteMenu' => 'https://api.weixin.qq.com/cgi-bin/menu/delete',
                
                // 创建分组
                'createGroup' => 'https://api.weixin.qq.com/cgi-bin/groups/create',
                
                // 修改分组名
                'updateGroup' => 'https://api.weixin.qq.com/cgi-bin/groups/update',
                
                // 移动用户分组
                'moveGroup' => 'https://api.weixin.qq.com/cgi-bin/groups/members/update',
                
                // 查询分组
                'getGroup' => 'https://api.weixin.qq.com/cgi-bin/groups/get',
                
                // 删除分组
                'deleteGroup' => 'https://api.weixin.qq.com/cgi-bin/groups/delete',
                
                // 查询用户所在分组
                'getGroupId' => 'https://api.weixin.qq.com/cgi-bin/groups/getid',
                
                // 设置用户备注名
                'userRemark' => 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark',
                
                // 获取用户列表
                'getOpenId' => 'https://api.weixin.qq.com/cgi-bin/user/get',
                
                // 获取用户基本信息
                'getUserInfo' => 'https://api.weixin.qq.com/cgi-bin/user/info',
                
                // 创建二维码ticket (临时二维码: 有效期30分钟)
                'createQrcode' => 'https://api.weixin.qq.com/cgi-bin/qrcode/create',
                
                // 通过ticket换取二维码
                'getQrcode' => 'https://mp.weixin.qq.com/cgi-bin/showqrcode',
                
                // 预览群发消息接口 (订阅号与服务号认证后均可用)
                'massPreview' => 'https://api.weixin.qq.com/cgi-bin/message/mass/preview',
                
                // 根据分组进行群发 (订阅号与服务号认证后均可用)
                'massAll' => 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall',
                
                // 根据OpenID列表群发 (订阅号不可用, 服务号认证后可用)
                'massOpenId' => 'https://api.weixin.qq.com/cgi-bin/message/mass/send',
                
                // 删除群发 (订阅号与服务号认证后均可用)
                'deleteMass' => 'https://api.weixin.qq.com/cgi-bin/message/mass/delete',
                
                // 查询群发消息发送状态 (订阅号与服务号认证后均可用)
                'getMass' => 'https://api.weixin.qq.com/cgi-bin/message/mass/get',
                
                // 新增永久图文素材
                'addNews' => 'https://api.weixin.qq.com/cgi-bin/material/add_news',
                
                // 新增其他类型永久素材
                // 分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
                'addMaterial' => 'https://api.weixin.qq.com/cgi-bin/material/add_material',
                
                // 删除永久图文素材
                'delMaterial' => 'https://api.weixin.qq.com/cgi-bin/material/del_material',
                
                // 下载用户素材
                'downloadMedia' => 'http://file.api.weixin.qq.com/cgi-bin/media/get',
                
                // 微信开放平台
                // 获取托管component_access_token
                'componentAccessToken' => 'https://api.weixin.qq.com/cgi-bin/component/api_component_token',
                // 获取托管预授权码
                'apiCreatePreauthcode' => 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode',
                // 微信授权页登入页面
                'componentLoginPage' => 'https://mp.weixin.qq.com/cgi-bin/componentloginpage',
                // 授权处理回调
                'componentApiQueryAuth' => 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth',
                // 获取刷新令牌authorizer_refresh_token
                'authorizerRefreshToken' => 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token',
                // 获取授权方的账户信息
                'apiGetAuthorizerInfo' => 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info',
                
                // 微信网页授权获取用户基本信息
                // 网页授权获取用户信息请求code
                'oauth2Authorize' => 'https://open.weixin.qq.com/connect/oauth2/authorize',
                // 开发者机制网页授权获取用户信息通过code换取access_token及openid
                'getAccessTokenOauth2' => 'https://api.weixin.qq.com/sns/oauth2/access_token',
                // 开发者机制通过网页授权access_token获取用户基本信息（需授权作用域为snsapi_userinfo）
                'getRefreshAccessTokenOauth2' => 'https://api.weixin.qq.com/sns/oauth2/refresh_token',
                // 授权机制网页授权获取用户信息通过code换取access_token及openid
                'oauth2Component' => 'https://api.weixin.qq.com/sns/oauth2/component/access_token',
                // 授权机制网页授权获取用户信息刷新access_token及openid（如果需要）
                'oauth2RefreshToken' => 'https://api.weixin.qq.com/sns/oauth2/component/refresh_token',
                // 授权机制通过网页授权access_token获取用户基本信息（需授权作用域为snsapi_userinfo）
                'oauth2UserInfo' => 'https://api.weixin.qq.com/sns/userinfo',
                
                // 上传接口所需图片(微信端)
                'uploadImg' => 'https://api.weixin.qq.com/cgi-bin/media/uploadimg',
              
            ),
        );
    }
}
