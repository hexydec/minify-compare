var esprima = require('esprima');

let js = "";

// read javascript from stdin
process.stdin.setEncoding("UTF-8");
process.stdin.on("readable", () => {
	const chunk = process.stdin.read();
	if (chunk !== null) {
		js += chunk;
	}
});
process.stdin.on("end", () => {
	const syntax = esprima.parseScript(js, {tolerant: true, loc: true, range: true});
	process.stdout.write(JSON.stringify(syntax.errors));
});
