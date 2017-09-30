
function checkSubnetMask(mask)
{
    var res = mask.split('.');

    if (res.length != 4) {
        return false;
    }

    if (parseInt(res[0]) === 0) {
        return false;
    }

    var binary = '';
    for (var i = 0; i < 4; i++) {
        var str = parseInt(res[i]).toString(2);

        if (str.length > 8) {
            return false;
        } else if (str.length < 8) {
            var prefix = new Array(8 - str.length + 1).join('0');
            str = prefix + str;
        }

        binary += '.'+str;
    }

    var pattern = new RegExp('0(?=1)');
    if (pattern.test(binary)) {
        return false;
    }

    return true;
}

// true
console.log(checkSubnetMask('255.255.0.0'));
console.log(checkSubnetMask('255.254.0.0'));
console.log(checkSubnetMask('255.248.0.0'));
// flase
console.log(checkSubnetMask('255.256.0.0'));
console.log(checkSubnetMask('255.12.0.0'));
console.log(checkSubnetMask('0.255.0.0'));
