# 整体设计架构 #

采用PHP+MySQL的方式，提供HTTP接口。
鉴于安全考虑，接口一律使用HTTPS传输，测试接口可以使用HTTP.

## Server环境 ##
Linux + MySQL + Apache(Nginx) + PHP

## 数据库结构 ##
数据库分为类似的两套表架构，一套保存所有的数据，称为全数据表，用后缀为\_all的表来标识，用户不能通过API对全数据表内容进行操作。另外一套则保存当前用户的数据，称为当前表。两者之间通过一系列定义在数据库中的触发器来完成如下的关系：
•	插入当前表的数据，将插入到全数据表同一ID的数据。
•	更新当前表的数据，将更新到全数据表同一ID的数据。
•	删除当前表的数据，将更新全数据表中同一ID的数据删除时间，并更新删除标记为1。
每套表本别包括, 一下四张，相互通过外键关联，对表的删除操作有级联删除的触发器。这意味这如果你删除一个用户，此用户相关的设备，传感器，数据都将删除。同时出发对全数据表的更新。
•	用户表 user
•	设备表 device
•	传感器表 sensor
•	数据表datapoint

数据库表结构，外键关联与唯一约束，请参考数据库描述文档。
# API介绍 #



| **Scheme** | **get** | **post** | **put** | **delete** |
|:-----------|:--------|:---------|:--------|:-----------|
|mobilecode|发送短信验证码|  |  |  |
|key|获得用户API-KEY|  |  |  |
|user|获得用户|新建用户|更新用户|删除用户|
|device|获得设备|新建设备|更新设备信息|删除设备|
|sensor|获得传感器|新建传感器|更新传感器|删除传感器|
|datapoint|获得数据|上传数据|更新数据|删除数据|

## 用户相关的API ##

### 发送短信验证码 ###
#### 输入 ####

HTTP Header

authCode的生成方法请直接联系我

```
GET http://198.23.226.199/innoapi/mobilecode HTTP/1.1
authCode: a8ab58430dbf2aadfc7e7cb83f061ffd
mobile: 13812345678
```
HTTP Body
```
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"success":1}
```
#### 异常情况 ####
1. authCode不正确: "Illegal calls, and it has been recorded."

2. 其它异常来自于短信平台
http://docs.yuntongxun.com/index.php/%E9%94%99%E8%AF%AF%E4%BB%A3%E7%A0%81


### 获得用户的KPI-KEY ###
#### 输入 ####

HTTP Header
```
GET http://198.23.226.199/innoapi/key HTTP/1.1
account: test1
password: test1
mobileCode: 000000
```
HTTP Body
```
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"key":"49a98af413554b98775a3a30931da4fd"}
```
#### 异常情况 ####
##### 输入数据不完整 #####
  1. account或者mobileCode至少要包含一个
  1. account必填
  * X-Status-Reason: Both password and mobile code are empty
##### 验证之后不正确 #####
  * X-Status-Reason: User name or mobile code is not correct or expired
    * 手机验证码有效期为5分钟
  * X-Status-Reason: User name or password is not correct

### 创建单个新用户 ###
#### 输入 ####

HTTP Header
```
POST http://198.23.226.199/innoapi/user HTTP/1.1
```
HTTP Body
```
{
	"usr_name": "test1",
	"usr_pwd":"test1",
	"usr_email":"test@test"
}
```
#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"usr_id":"2"}
```
#### 异常情况 ####
##### 输入数据不完整 #####

输入1：usr\_pwd 缺失
HTTP Header
```
POST http://198.23.226.199/innoapi/user HTTP/1.1
```
HTTP Body
```
{
	"usr_name": "test1",
	"usr_email":"test@test"
}
```
输入2：usr\_name缺失
HTTP Header
```
POST http://198.23.226.199/innoapi/user HTTP/1.1
```
HTTP Body
```
{
	"usr_pwd":"test1",
	"usr_email":"test@test"
}
```
输入3：整个信息缺失
HTTP Header
```
POST http://198.23.226.199/innoapi/user HTTP/1.1
```
HTTP Body
```
```

输出：
HTTP Header
```
HTTP/1.1 400 Bad Request
X-Status-Reason: User name and password is the mandatory parameters
```
HTTP Body
```
```

##### 用户名重复 #####

输入：test1已经存在于数据库中

HTTP Header
```
POST http://198.23.226.199/innoapi/user HTTP/1.1
```
HTTP Body
```
{
	"usr_name": "test1",
	"usr_pwd":"test1",
	"usr_email":"test@test"
}
```
输出：
HTTP Header
```
HTTP/1.1 400 Bad Request
Date: Mon, 25 Nov 2013 12:25:00 GMT
Server: Apache/2.4.4 (Unix) OpenSSL/1.0.1e PHP/5.5.3 mod_perl/2.0.8-dev Perl/v5.16.3
X-Powered-By: PHP/5.5.3
X-Status-Reason: User name is already existed!

```
HTTP Body
```
```

### 获得单个用户的信息 ###
#### 输入 ####

HTTP Header
```
GET http://198.23.226.199/innoapi/user/1 HTTP/1.1
API-KEY:427315b472e04c41c1eb91316c4e66ad
```
HTTP Body
```
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"usr_id":"1","usr_name":"test1","usr_email":"test@test","usr_key":"427315b472e04c41c1eb91316c4e66ad","usr_created_time":"2013-11-20 10:14:39","usr_modified_time":"2013-11-20 10:14:39"}
```
#### 异常情况 ####

##### HTTP请求中未包含API-KEY或者不正确 #####

输入1：API-KEY缺失
HTTP Header
```
GET http://198.23.226.199/innoapi/user/1 HTTP/1.1
```
HTTP Body
```
```

输入2：给定的API-KEY找不到任何匹配的用户
HTTP Header
```
GET http://198.23.226.199/innoapi/user/1 HTTP/1.1
API-KEY:427315b472e04c41c1eb91316c4e66ad
```
HTTP Body
```
```

输出：
HTTP Header
```
HTTP/1.1 400 Bad Request
X-Status-Reason: Invalid API-KEY
```
HTTP Body
```
```

##### HTTP请求URL中用户ID与API-KEY不匹配 #####

输入：给定的API-KEY所对应的用户ID不是1
HTTP Header
```
GET http://198.23.226.199/innoapi/user/1 HTTP/1.1
API-KEY:427315b472e04c41c1eb91316c4e66ad
```
HTTP Body
```
```

输出：
HTTP Header
```
HTTP/1.1 400 Bad Request
X-Status-Reason: You are not allowed to see other user
```
HTTP Body
```
```

### 更新单个用户信息 ###
#### 输入 ####

HTTP Header
```
PUT http://198.23.226.199/innoapi/user/1 HTTP/1.1
API-KEY: 427315b472e04c41c1eb91316c4e66ad
```
HTTP Body
```
{
	"usr_name": "test1",
	"usr_pwd":"test2",
	"usr_email":"test@test"
}
```
#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"success":1}
```

#### 异常情况 ####

##### HTTP请求URL中用户ID与API-KEY不匹配 #####

输入：给定的API-KEY所对应的用户ID不是2
HTTP Header
```
PUT http://198.23.226.199/innoapi/user/2 HTTP/1.1
API-KEY:427315b472e04c41c1eb91316c4e66ad
```
HTTP Body
```
```

输出：
HTTP Header
```
HTTP/1.1 400 Bad Request
X-Status-Reason: You are not allowed to update other user
```
HTTP Body
```
```

##### 输入数据不完整 #####

输入1：usr\_pwd 缺失
HTTP Header
```
PUT  http://198.23.226.199/innoapi/user/1 HTTP/1.1
API-KEY:427315b472e04c41c1eb91316c4e66ad
```
HTTP Body
```
{
	"usr_name": "test1",
	"usr_email":"test@test"
}
```
输入2：usr\_name缺失
HTTP Header
```
PUT  http://198.23.226.199/innoapi/user/1 HTTP/1.1
API-KEY:427315b472e04c41c1eb91316c4e66ad
```
HTTP Body
```
{
	"usr_pwd":"test1",
	"usr_email":"test@test"
}
```

输入3：整个信息缺失
HTTP Header
```
PUT  http://198.23.226.199/innoapi/user/1 HTTP/1.1
API-KEY:427315b472e04c41c1eb91316c4e66ad
```
HTTP Body
```
```

输出：
HTTP Header
```
HTTP/1.1 400 Bad Request
X-Status-Reason: User name and password is the mandatory parameters

```
HTTP Body
```
```

### 删除单个用户 ###
#### 输入 ####

HTTP Header
```
DELETE http://198.23.226.199/innoapi/user/2 HTTP/1.1
API-KEY: 427315b472e04c41c1eb91316c4e66ad
```
HTTP Body
```
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"success":1}
```
#### 异常输出 ####
##### HTTP请求URL中用户ID与API-KEY不匹配 #####

输入：给定的API-KEY所对应的用户ID不是2
HTTP Header
```
DELETE http://198.23.226.199/innoapi/user/2 HTTP/1.1
API-KEY: 427315b472e04c41c1eb91316c4e66ad
```
HTTP Body
```
```

输出：
HTTP Header
```
HTTP/1.1 400 Bad Request
X-Status-Reason: You are not allowed to delete other user
```
HTTP Body
```
```

## 设备相关的API ##
### 创建单个新的设备 ###
#### 输入 ####

HTTP Header
```
POST http://198.23.226.199/innoapi/device HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
{
	"dev_name":"test1",
	"dev_sn":"test_sn_ssssss",
	"dev_desc":"no desc test 1",
	"dev_lat":0.444,
	"dev_lon":0.555
}
```
#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"dev_id":"1"}
```

#### 异常情况 ####
##### 设备串号重复 #####

输入：test\_sn\_ssssss已经存在于数据库中
HTTP Header
```
POST http://198.23.226.199/innoapi/device HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
{
	"dev_name":"test1",
	"dev_sn":"test_sn_ssssss",
	"dev_desc":"no desc test 1",
	"dev_lat":0.444,
	"dev_lon":0.555
}
```

输出：
HTTP Header
```
HTTP/1.1 400 Bad Request
X-Status-Reason: dev_sn is already existed!

```
HTTP Body
```
```

### 获得单个设备的信息 ###
#### 输入 ####

HTTP Header
```
GET http://198.23.226.199/innoapi/device/1 HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{
	"dev_id":"1",
	"usr_id":"2",
	"dev_name":"test1",
	"dev_sn":"test_sn_ssssss",
	"dev_desc":"no desc test 1",
	"dev_lat":"0.444",
	"dev_lon":"0.555",
	"dev_created_time":"2013-11-25 16:07:02",
	"dev_modified_time":"2013-11-25 16:07:02"
}
```

#### 异常输出 ####
##### 没找到相应的设备 #####

输入：设备ID为2的记录不存在或不属于该API-KEY所对应的用户
HTTP Header
```
GET http://198.23.226.199/innoapi/device/1 HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
{
	"dev_name":"test1",
	"dev_sn":"test_sn_ssssss",
	"dev_desc":"no desc test 1",
	"dev_lat":0.444,
	"dev_lon":0.555
}
```

输出：
HTTP Header
```
HTTP/1.1 400 Bad Request
X-Status-Reason: No records
```
HTTP Body
```
```

### 获得当前用户所有设备的信息 ###
#### 输入 ####

HTTP Header
```
GET http://198.23.226.199/innoapi/device HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
[
	{
		"dev_id":"1",
		"usr_id":"2",
		"dev_name":"test1",
		"dev_sn":"test_sn_ssssss",
		"dev_desc":"no desc test 1",
		"dev_lat":"0.444",
		"dev_lon":"0.555",
		"dev_created_time":"2013-11-25 16:07:02",
		"dev_modified_time":"2013-11-25 16:07:02"
	},
	{
		"dev_id":"2",
		"usr_id":"2",
		"dev_name":"test1",
		"dev_sn":"test_sn_ssss2",
		"dev_desc":"no desc test 1",
		"dev_lat":"0.444",
		"dev_lon":"0.555",
		"dev_created_time":"2013-11-25 16:19:46",
		"dev_modified_time":"2013-11-25 16:19:46"
	}
]
```

#### 异常输出 ####
##### 没找到相应的设备 #####

输入：没有找到属于该API-KEY所对应的用户的设备
HTTP Header
```
GET http://198.23.226.199/innoapi/device HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
```

输出：
HTTP Header
```
HTTP/1.1 400 Bad Request
X-Status-Reason: No records
```
HTTP Body
```
```

### 更新单个设备的信息 ###
#### 输入 ####

HTTP Header
```
PUT http://198.23.226.199/innoapi/device/1 HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
{
	"dev_name":"test1",
	"dev_sn":"test_sn_sssss1",
	"dev_desc":"no desc test 1",
	"dev_lat":0.444,
	"dev_lon":0.555
}
```
#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"success":1}
```
#### 异常输出 ####

### 删除单个设备 ###
#### 输入 ####

HTTP Header
```
DELETE http://198.23.226.199/innoapi/device/2 HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"success":1}
```

### 异常输出 ###

## 传感器相关的API ##
### 创建单个新的传感器 ###
#### 输入 ####

HTTP Header
```
POST http://198.23.226.199/innoapi/device/1/sensor HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
{
	"sen_name":"sensor1",
	"sen_desc":"test sensor",
	"sen_unit":"Temp",
	"sen_unit_symbol":"C"
}
```
#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"sen_id":"1"}
```

#### 异常输出 ####

### 获得单个传感器的信息 ###
#### 输入 ####

HTTP Header
```
GET http://198.23.226.199/innoapi/device/1/sensor/1 HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
[
	{
		"sen_id":"1",
		"dev_id":"1",
		"sen_name":"sensor1",
		"sen_desc":"test sensor",
		"sen_unit":"Temp",
		"sen_unit_symbol":"C",
		"sen_created_time":"2013-11-25 16:28:46",
		"sen_modified_time":"2013-11-25 16:28:46",
		"usr_id":"2"
	}
]
```

#### 异常输出 ####

### 获得指定设备下所有的传感器信息 ###
#### 输入 ####

HTTP Header
```
GET http://198.23.226.199/innoapi/device/1/sensor HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
```
#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
[
	{
		"sen_id":"1",
		"dev_id":"1",
		"sen_name":"sensor1",
		"sen_desc":"test sensor",
		"sen_unit":"Temp",
		"sen_unit_symbol":"C",
		"sen_created_time":"2013-11-25 16:28:46",
		"sen_modified_time":"2013-11-25 16:28:46",
		"usr_id":"2"
	},
	{
		"sen_id":"2",
		"dev_id":"1",
		"sen_name":"sensor1",
		"sen_desc":"test sensor",
		"sen_unit":"Temp",
		"sen_unit_symbol":"C",
		"sen_created_time":"2013-11-25 16:31:00",
		"sen_modified_time":"2013-11-25 16:31:00",
		"usr_id":"2"
	}
]
```

#### 异常输出 ####

### 更新单个传感器信息 ###
#### 输入 ####

HTTP Header
```
PUT http://198.23.226.199/innoapi/device/1/sensor/2 HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
{
	"sen_name":"sensor2",
	"sen_desc":"test sensor2",
	"sen_unit":"Temp",
	"sen_unit_symbol":"C"
}
```
#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"success":1}
```
#### 异常输出 ####

### 删除单个传感器 ###
#### 输入 ####

HTTP Header
```
DELETE http://198.23.226.199/innoapi/device/1/sensor/2 HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"success":1}
```

#### 异常输出 ####

## 数据相关的API ##
### 写入单个数据 ###
#### 输入 ####
##### 单值类型 #####
HTTP Header
```
POST http://198.23.226.199/innoapi/device/1/sensor/1/datapoint HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
{
"timestamp":"2012-03-15T16:13:14",
"value":294.34,
"type": "value"
}
```
##### 其它Json类型 #####
HTTP Header
```
POST http://198.23.226.199/innoapi/device/1/sensor/1/datapoint HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
{
	"timestamp":"2012-03-15T16:13:14",
	"value":{"lat":35.4567,"lng":46.1234,"speed":98.2,"offset":"yes"},
	"type":"gen"
}
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"dat_id":"2"}
```

#### 异常输出 ####

### 根据指定的时间段读取所有相关数据 ###
#### 输入 ####

HTTP Header
```
GET http://198.23.226.199/innoapi/device/1/sensor/1/datapoint HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
starttime: 2012-03-11 13:14
endtime: 2013-03-16 5:24
```
HTTP Body
```
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
[
	{
		"dat_id":"1",
		"sen_id":"1",
		"dat_time":"2012-03-15 16:13:14",
		"dat_value":"294.34",
		"dat_type":"value",
		"dat_created_time":"2013-11-25 16:35:01",
		"dat_modified_time":"2013-11-25 16:35:01"
	},
	{
		"dat_id":"2",
		"sen_id":"1",
		"dat_time":"2012-03-15 16:13:14",
		"dat_value": {
				   "lat":35.4567,
				  "lng":46.1234,
				  "speed":98.2,
				  "offset":"yes"
		},
		"dat_type":"gen",
		"dat_created_time":"2013-11-25 16:38:03",
		"dat_modified_time":"2013-11-25 16:38:03"
	}
]
```

#### 异常输出 ####

### 读取单个数据 ###
#### 输入 ####

HTTP Header
```
GET http://198.23.226.199/innoapi/device/1/sensor/1/datapoint/1 HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
```

#### 输出 ####
HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{
	"dat_id":"1",
	"sen_id":"1",
	"dat_time":"2012-03-15 16:13:14",
	"dat_value":"294.34","dat_type":"value",
	"dat_created_time":"2013-11-25 16:35:01",
	"dat_modified_time":"2013-11-25 16:35:01",
	"dev_id":"1",
	"usr_id":"2"
}
```

#### 异常输出 ####

### 更新单个数据 ###
#### 输入 ####

HTTP Header
```
PUT http://198.23.226.199/innoapi/device/1/sensor/1/datapoint/1 HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
{
	"timestamp":"2012-03-15T16:13:14",
	"value":274.34,
	"type": "value"
}
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"success":1}
```

#### 异常输出 ####

### 删除单个数据 ###
#### 输入 ####

HTTP Header
```
DELETE http://198.23.226.199/innoapi/device/1/sensor/1/datapoint/1 HTTP/1.1
API-KEY: 8d37d11a685ba18a46a1b3c207ea969b
```
HTTP Body
```
```

#### 输出 ####

HTTP Header
```
HTTP/1.1 200 OK
Content-Type: application/json
```
HTTP Body
```
{"success":1}
```

#### 异常输出 ####