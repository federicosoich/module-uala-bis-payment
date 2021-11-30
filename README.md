# FS_UalaBis for Magento2

This Extension is used to make payments using Uala Bis API in Argentina.

- Allow end user set credentials, checkout message and name of payment method 
- Generate pending orders and using return parameters to change to cancel or processing (and invoice)
- Add validation to prevent unexpected changes on status using urls
- Can be used in production / test mode just changing credentials



## Manual Installation

- Create a folder [root]/app/code/FS/UalaBis
- Download module ZIP
- Copy to folder


Then you'll need to activate the module.

```
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
bin/magento cache:flush

```

## Uninstall

```
bin/magento module:uninstall FS_UalaBis
```

## Support

No warranty or support provided.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

### How to create a PR

1. Fork it
2. Create your feature branch (git checkout -b my-new-feature)
3. Commit your changes (git commit -am 'Add some feature')
4. Push to the branch (git push origin my-new-feature)
5. Create new Pull Request

## License

[MIT](https://choosealicense.com/licenses/mit/)
