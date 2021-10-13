# library

## 说明

fingerRed PHP 基础 Lib，提供一些基础服务能力。

## 原则

- 无需配置即可使用，也可自己实例化配置使用
  - 自有默认配置
  - 基于 RedConfig 做默认配置
- 容错性和保护性设计（辅助功能即使不正常也不影响业务，比如 RedLog）
  - 尽量不暴露内部的成员变量
  - 采用 protected 保持扩展性
  - 内部检查文件存储的权限等
- 静态方法调用，减少使用复杂度
  - 静态方法是动态方法的引用


## 使用

添加到 `composer.json`

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": ""
        }
    ]
}
```

安装

```bash
composer require fingerred/lib
```

升级

```bash
composer update fingerred/lib
```
