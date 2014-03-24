# ArbreDelais
{ArbreDelais, ArbrePrix} = require("./classes.js")
assert = require("assert")

console.log "Testing ArbreDelais"
# ArbreDelais
a = new ArbreDelais 6
assert.equal a.getTotal(), 6, "Initializes ArbreDelais"

a.ajout_enfants (new ArbreDelais(i) for i in [-3..78])
assert.equal a.getTotal(), 78, "Computes the maximum"

enf = new ArbreDelais(115)
a.ajout_enfant enf
assert.equal a.getTotal(), 115, "ajout_enfant"

enf.setVal('200')
assert.strictEqual enf.getTotal(), 200, 'setValue'

assert.equal a.getTotal(), 200, 'update'

console.log "Testing ArbrePrix"
# ArbrePrix
a = new ArbrePrix 10
a.setNbr 10
assert.equal a.getTotal(), 10*10, "Initializes"

a.ajout_enfants (new ArbreDelais(i) for i in [-100..101])
assert.equal a.getTotal(), 10*(10+101), "Computes the sum"

console.log "All tests passed"
