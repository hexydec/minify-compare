import $ from "../../node_modules/dabbyjs/src/core/dabby/dabby.js";
import "../../node_modules/dabbyjs/src/core/each/each.js";
import "../../node_modules/dabbyjs/src/attributes/attr/attr.js";
import "../../node_modules/dabbyjs/src/attributes/css/css.js";
import "../../node_modules/dabbyjs/src/utils/each/each.js";
import "../../node_modules/dabbyjs/src/traversal/eq/eq.js";
import "../../node_modules/dabbyjs/src/attributes/data/data.js";
import "../../node_modules/dabbyjs/src/manipulation/html/html.js";
import "../../node_modules/dabbyjs/src/manipulation/text/text.js";
import "../../node_modules/dabbyjs/src/manipulation/insert/insert.js";
import "../../node_modules/dabbyjs/src/traversal/add/add.js";
import "../../node_modules/dabbyjs/src/traversal/children/children.js";
import "../../node_modules/dabbyjs/src/traversal/next-prev/next-prev.js";
import "../../node_modules/dabbyjs/src/attributes/class/class.js";
import "../../node_modules/dabbyjs/src/events/on/on.js";

class compare {

	constructor(obj, config) {
		this.config = Object.assign({
			addcls: "minify__",
			threads: 4,
			dataattr: "compare",
			inputfields: ["input", "inputgzip", "inputerrors"],
			outputfields: ["time", "output", "diff", "ratio", "outputgzip", "diffgzip", "ratiogzip", "outputerrors"],
			comparefields: ["ratio", "ratiogzip", "time"]
		}, config || {});
		this.config.cls = "."+this.config.addcls;
		this.progress = $(this.config.cls + "progress");
	}

	run(rows) {
		this.compare = rows;
		const len = this.compare.length,
			percentage = 100 / len,
			promises = [];
		this.resolvers = [];
		this.i = 0;
		this.data = [];

		// show the progress bar
		this.progress.addClass(this.config.addcls + "progress--running");

		// create promises for each loop
		let run = 0;
		for (let p = 0; p < len; p++) {
			promises.push(new Promise(resolve => {
				this.resolvers.push(() => {
					run++;
					this.progress
						.css("--progress", (percentage * run) + "%")
						.text("Completed test " + run + " of " + len);
					resolve();
				});
			}));
		}

		// when all promises are resolved
		Promise.allSettled(promises).then(() => {
			this.progress.removeClass(this.config.addcls + "progress--running");
			this.totals(this.data);
		});


		// set each thread going
		for (let n = 0; n < Math.min(this.config.threads, this.compare.length); n++) {
			this.next();
		}
	}

	format(key, val, data) {
		const format = {
			ratio: val => parseFloat(val).toFixed(2) + "%",
			ratiogzip: val => parseFloat(val).toFixed(2) + "%",
			time: val => parseFloat(val).toFixed(8),
			inputerrors: this.validator,
			outputerrors: this.validator
		};
		if (format[key]) {
			return format[key](val, data);

		// format the other values
		} else {
			return new Intl.NumberFormat("en-GB").format(Math.round(val));
		}
	}

	validator(val, data) {
		if (val) {
			let validator = $();
			val.forEach(item => {
				validator = validator.add($("<li>", {text: item}));
			});
			const output = typeof data.outputerrors !== "undefined",
				nodes = $("<div>")
					.append($("<input>", {
						type: "checkbox",
						name: "popup",
						id: "popup-" + (output ? "out" : "in") + "put-" + data.index,
						"class": "minify__popup-switch"
					}))
					.append($("<label>", {
						for: "popup-" + (output ? "out" : "in") + "put-" + data.index,
						"class": "minify__popup-label " + (val.length > 0 ? "icon-cross" : "icon-tick"),
						title: "View validation results"
					}))
					.append(
						$("<div>", {"class": "minify__popup"})
							.append(
								$("<div>", {"class": "minify__popup-inner"})
									.append($("<label>", {
										"class": "minify__popup-close icon-cross",
										for: "popup-" + (output ? "out" : "in") + "put-" + data.index,
										text: "Close"
									}))
									.append($("<h2>", {"class": "minify__popup-heading", text: (output ? "Out" : "In") + "put Validation"}))
									.append($("<p>").append($("<a>", {href: data.url, target: "_blank", rel: "noopener", text: data.url})))
									.append(
										$("<h3>", {
											"class": "minify__popup-subheading",
											text: "The " + (output ? "out" : "in") + "put contained " + (val.length ? val.length + " errors" : "no errors")
										})
											.append(
												$("<a>", {
													href: data.code || data.url,
													target: "_blank",
													title: "View source code",
													"class": "minify__popup-code icon-code"
												})
											)
									)
									.append(
										$("<ul>", {"class": "minify__popup-output"}).append(validator)
									)
							)
					);
			return nodes.html();
		} else if (data.code || data.url) {
			return $("<div>").append($("<a>", {
				href: data.code || data.url,
				target: "_blank",
				title: "View source code",
				"class": "minify__popup-code icon-code"
			})).html();
		}
	}

	bestAndWorst(values, flip) {
		const metrics = {
			best: [],
			worst: []
		};
		$.each(metrics, m => {
			values.forEach((item, i) => {
				if (item !== null) {
					const gt = m === (flip ? "worst" : "best");
					if (!metrics[m].length) {
						metrics[m] = [i];
					} else if (gt ? values[metrics[m][0]] <= item : values[metrics[m][0]] >= item) {
						if (values[metrics[m][0]] !== item) {
							metrics[m] = [i];
						} else {
							metrics[m].push(i);
						}
					}
				}
			});
		});
		return metrics;
	}

	next() {
		const i = this.i++,
			$this = this.compare.eq(i),
			url = $this.data(this.config.dataattr),
			rows = $this.add($this.next()),
			callback = response => {
				if (response.ok) {
					response.json().then(json => {
						const metrics = ["best", "worst"];

						// add input sizes
						this.config.inputfields.forEach(key => {
							$(this.config.cls + key, rows).html(this.format(key, json[key], json));
						});

						// add output data
						this.config.outputfields.forEach(key => {
							const cells = $(this.config.cls + key, rows),
								minifiers = [],
								results = {},
								values = [];
							let n = 0;
							$.each(json.minifiers, (min, data) => {
								minifiers.push(min);

								// store best and worst
								if (this.config.comparefields.indexOf(key) > -1) {
									if (data.irregular) {
										cells.eq(n).addClass(this.config.addcls + "table--failed");
									}
									values.push(data.irregular ? null : data[key]);
								}

								// render the value
								cells.eq(n++).html(this.format(key, data[key], data));
							});

							// write best and worst
							if (values.length) {
								$.each(this.bestAndWorst(values, key === "time"), (type, index) => {
									index.forEach(i => {
										cells.eq(i).addClass(this.config.addcls + "table--" + type);
									});
								});
							}
						});

						// save data to calculate totals/averages
						this.data.push(json);
						this.resolvers[i](); // resolve this promise
					}).catch(e => {
						this.resolvers[i](); // resolve this promise
					});
				} else {
					this.resolvers[i](); // resolve this promise
				}

				// make the next call
				if (this.i < this.compare.length) {
					this.next();
				}
			};
		fetch(location.href + "?index=" + url).then(callback);
	}

	totals(data) {
		let total = $(this.config.cls + "total"),
			avg = $(this.config.cls + "averages"),
			input = {};

		total = total.add(total.next());
		avg = avg.add(avg.next());

		// input fields
		this.config.inputfields.forEach(key => {
			const totalcells = $(this.config.cls + key, total),
				avgcells = $(this.config.cls + key, avg);

			if (totalcells.length + avgcells.length) {
				input[key] = 0;

				// tot up the input
				data.forEach(item => {
					input[key] += item[key];
				});

				// render
				totalcells.html(this.format(key, input[key], input));
				avgcells.html(this.format(key, input[key] / data.length));
			}
		});

		// collate output fields
		let values = [];
		data.forEach((item, n) => {

			// tot up the output
			let i = 0;
			$.each(item.minifiers, (min, row) => {
				values[i] = values[i] || {};
				this.config.outputfields.forEach(key => {
					values[i][key] = values[i][key] || 0;
					if (!row.irregular) {
						values[i][key] += row[key];
					}
				});
				i++;
			});
		});

		// render output fields
		const len = data.length;
		this.config.outputfields.forEach(key => {
			const totalcells = $(this.config.cls + key, total),
				avgcells = $(this.config.cls + key, avg),
				totals = [];

			// render values
			if (totalcells.length + avgcells.length) {
				values.forEach((item, i) => {
					const suffix = key === "ratiogzip" ? "gzip" : "",
						avg = ["ratio", "ratiogzip"].indexOf(key) > -1,
						val = avg ? (100 / input["input" + suffix]) * item["diff" + suffix] * -1 : item[key];
					totalcells.eq(i).html(this.format(key, val));
					avgcells.eq(i).html(this.format(key, avg ? val : val / len));
					if (val) {
						totals[i] = val;
					}
				});

				// best and worst
				if (this.config.comparefields.indexOf(key) > -1) {
					$.each(this.bestAndWorst(totals, key === "time"), (type, index) => {
						index.forEach(i => {
							totalcells.eq(i).add(avgcells.eq(index)).addClass(this.config.addcls + "table--" + type);
						});
					});
				}
			}
		});
	}
}

$(() => {
	$(".minify__start").one("click", function () {
		this.style.display = "none";
		const obj = new compare();
		obj.run($("[data-compare]"));
	});
});
